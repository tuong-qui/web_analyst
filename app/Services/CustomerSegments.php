<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerSegments
{
    public const GROUPS = [
        'champions' => ['name' => 'Khách hàng giá trị cao', 'rule' => 'R ≥ 4, F ≥ 3, M ≥ 4', 'action' => 'Chăm sóc riêng và ưu đãi thành viên.'],
        'loyal' => ['name' => 'Khách hàng trung thành', 'rule' => 'R ≥ 3, F ≥ 3', 'action' => 'Gợi ý mua thêm và chương trình tích điểm.'],
        'new' => ['name' => 'Khách hàng mới', 'rule' => 'R ≥ 4, F = 1', 'action' => 'Chăm sóc sau mua và khuyến khích đơn tiếp theo.'],
        'at_risk' => ['name' => 'Có nguy cơ rời bỏ', 'rule' => 'R ≤ 2, F ≥ 2', 'action' => 'Nhắc quay lại và ưu đãi phù hợp.'],
        'inactive' => ['name' => 'Ít hoạt động', 'rule' => 'R ≤ 2, F = 1', 'action' => 'Thử chiến dịch kích hoạt lại.'],
        'potential' => ['name' => 'Khách hàng tiềm năng', 'rule' => 'Các trường hợp còn lại', 'action' => 'Khuyến khích tăng tần suất mua.'],
    ];

    public function analyze(int $datasetId, ?string $date = null): array
    {
        $base = DB::table('orders as o')->where('o.dataset_id', $datasetId)
            ->whereNotNull('o.order_purchase_timestamp')
            ->where(fn ($q) => $q->whereNull('o.order_status')->orWhereNotIn('o.order_status', ['canceled', 'unavailable']))
            ->join('customers as c', fn ($join) => $join->on('c.dataset_id', '=', 'o.dataset_id')->on('c.customer_id', '=', 'o.customer_id'))
            ->whereNotNull('c.customer_unique_id')->whereRaw("TRIM(c.customer_unique_id) <> ''");
        $latest = (clone $base)->max('o.order_purchase_timestamp');
        $reference = $date ? Carbon::parse($date)->startOfDay() : ($latest ? Carbon::parse($latest)->startOfDay() : null);
        $payments = DB::table('order_payments')->where('dataset_id', $datasetId)
            ->select('dataset_id', 'order_id')->selectRaw('SUM(payment_value) as value')->groupBy('dataset_id', 'order_id');
        $metrics = $base->where('o.order_purchase_timestamp', '<', $reference?->copy()->addDay()->toDateTimeString() ?? '0001-01-01')
            ->leftJoinSub($payments, 'p', fn ($join) => $join->on('p.dataset_id', '=', 'o.dataset_id')->on('p.order_id', '=', 'o.order_id'))
            ->selectRaw('TRIM(c.customer_unique_id) as customer, DATE(MAX(o.order_purchase_timestamp)) as last_purchase, COUNT(*) as frequency, COALESCE(SUM(p.value), 0) as monetary')
            ->groupByRaw('TRIM(c.customer_unique_id)');

        // Read only scalar amounts for percentile boundaries, never all customer records.
        $amounts = DB::query()->fromSub(clone $metrics, 'metrics')->orderBy('monetary')->pluck('monetary');
        $total = $amounts->count();
        $cuts = collect([0.2, 0.4, 0.6, 0.8])->map(fn ($q) => $total ? (float) $amounts[(int) ceil($total * $q) - 1] : 0)->all();
        unset($amounts);
        $recencySql = DB::connection()->getDriverName() === 'sqlite'
            ? 'CAST(julianday(?) - julianday(last_purchase) AS INTEGER)'
            : 'DATEDIFF(?, last_purchase)';
        $dated = DB::query()->fromSub($metrics, 'metrics')->select('metrics.*')
            ->selectRaw($recencySql.' as recency', [$reference?->toDateString()]);
        $scored = DB::query()->fromSub($dated, 'dated')->select('dated.*')
            ->selectRaw('CASE WHEN recency <= 30 THEN 5 WHEN recency <= 60 THEN 4 WHEN recency <= 90 THEN 3 WHEN recency <= 180 THEN 2 ELSE 1 END as r')
            ->selectRaw('CASE WHEN frequency >= 5 THEN 5 ELSE frequency END as f')
            ->selectRaw('CASE WHEN monetary <= 0 THEN 1 ELSE 1 + (CASE WHEN monetary > CAST(? AS DECIMAL(20, 6)) THEN 1 ELSE 0 END) + (CASE WHEN monetary > CAST(? AS DECIMAL(20, 6)) THEN 1 ELSE 0 END) + (CASE WHEN monetary > CAST(? AS DECIMAL(20, 6)) THEN 1 ELSE 0 END) + (CASE WHEN monetary > CAST(? AS DECIMAL(20, 6)) THEN 1 ELSE 0 END) END as m', $cuts);
        $classified = DB::query()->fromSub($scored, 'scored')->select('scored.*')->selectRaw("CASE
            WHEN r >= 4 AND f >= 3 AND m >= 4 THEN 'champions'
            WHEN r >= 3 AND f >= 3 THEN 'loyal'
            WHEN r >= 4 AND f = 1 THEN 'new'
            WHEN r <= 2 AND f >= 2 THEN 'at_risk'
            WHEN r <= 2 THEN 'inactive' ELSE 'potential' END as segment");
        $customers = DB::query()->fromSub($classified, 'classified');
        $summary = (clone $customers)->select('segment')->selectRaw('COUNT(*) as total, SUM(monetary) as monetary, SUM(frequency) as orders')
            ->groupBy('segment')->get()->keyBy('segment');
        $groups = collect(self::GROUPS)->map(fn ($group, $key) => array_merge($group, [
            'count' => (int) ($summary->get($key)?->total ?? 0),
            'monetary' => (float) ($summary->get($key)?->monetary ?? 0),
        ]));

        return ['date' => $reference?->toDateString(), 'customers' => $customers, 'groups' => $groups,
            'total' => $total, 'monetary' => (float) $groups->sum('monetary'), 'orders' => (int) $summary->sum('orders')];
    }
}
