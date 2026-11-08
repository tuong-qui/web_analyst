<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class HomeAnalytics
{
    public function forDataset(int $datasetId): array
    {
        $orders = DB::table('orders')->where('dataset_id', $datasetId);
        $payments = DB::table('order_payments')->where('dataset_id', $datasetId);
        $totalOrders = (clone $orders)->count();
        $totalPayments = (float) (clone $payments)->sum('payment_value');
        $paidOrders = (clone $payments)->distinct()->count('order_id');
        $delivered = (clone $orders)->where('order_status', 'delivered')->count();

        // Aggregate payments before joining: an order may have several payment rows.
        $paymentTotals = (clone $payments)->select('dataset_id', 'order_id')
            ->selectRaw('SUM(payment_value) as value')->groupBy('dataset_id', 'order_id');
        $monthly = DB::table('orders as o')->where('o.dataset_id', $datasetId)
            ->leftJoinSub($paymentTotals, 'p', function ($join) {
                $join->on('p.dataset_id', '=', 'o.dataset_id')->on('p.order_id', '=', 'o.order_id');
            })
            ->whereNotNull('o.order_purchase_timestamp')
            ->selectRaw('SUBSTR(o.order_purchase_timestamp, 1, 7) as month, COUNT(*) as orders, COALESCE(SUM(p.value), 0) as value')
            ->groupByRaw('SUBSTR(o.order_purchase_timestamp, 1, 7)')->orderBy('month')->get();

        return [
            'orders' => $totalOrders,
            'customers' => DB::table('customers')->where('dataset_id', $datasetId)
                ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(customer_unique_id), '')) as total")->value('total'),
            'payments' => $totalPayments,
            'average' => $paidOrders ? $totalPayments / $paidOrders : 0,
            'deliveryRate' => $totalOrders ? $delivered / $totalOrders * 100 : 0,
            'undatedOrders' => (clone $orders)->whereNull('order_purchase_timestamp')->count(),
            'monthly' => $monthly,
            'statuses' => (clone $orders)->selectRaw("COALESCE(NULLIF(order_status, ''), 'unknown') as label, COUNT(*) as value")
                ->groupBy('order_status')->orderByDesc('value')->get(),
            'paymentTypes' => (clone $payments)->selectRaw("COALESCE(NULLIF(payment_type, ''), 'unknown') as label, SUM(payment_value) as value")
                ->groupBy('payment_type')->orderByDesc('value')->get(),
            'categories' => DB::table('order_items as i')->where('i.dataset_id', $datasetId)
                ->leftJoin('products as p', function ($join) {
                    $join->on('p.dataset_id', '=', 'i.dataset_id')->on('p.product_id', '=', 'i.product_id');
                })
                ->selectRaw("COALESCE(NULLIF(p.product_category_name, ''), 'Chưa phân loại') as label, SUM(i.price) as value")
                ->groupByRaw("COALESCE(NULLIF(p.product_category_name, ''), 'Chưa phân loại')")
                ->orderByDesc('value')->limit(8)->get(),
        ];
    }
}
