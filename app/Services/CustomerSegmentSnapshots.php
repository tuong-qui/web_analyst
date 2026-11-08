<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerSegmentSnapshots
{
    public function save(int $datasetId, ?string $date, Builder $customers): void
    {
        if ($date === null) {
            return;
        }

        DB::transaction(function () use ($datasetId, $date, $customers) {
            // Serialize replacement for a dataset; other dates remain intact.
            DB::table('datasets')->where('dataset_id', $datasetId)->lockForUpdate()->firstOrFail();

            DB::table('customer_segments')->where('dataset_id', $datasetId)
                ->where('analysis_date', $date)->delete();

            $snapshot = DB::query()->fromSub(clone $customers, 'snapshot')
                ->selectRaw('? as dataset_id, ? as analysis_date', [$datasetId, $date])
                ->addSelect('customer', 'last_purchase', 'recency', 'frequency', 'monetary', 'r', 'f', 'm', 'segment')
                ->selectRaw('? as analyzed_at', [now()->toDateTimeString()]);

            // Copy the full analysis in SQL, before UI filtering and pagination.
            DB::table('customer_segments')->insertUsing([
                'dataset_id', 'analysis_date', 'customer_unique_id', 'last_purchase',
                'recency', 'frequency', 'monetary', 'r_score', 'f_score', 'm_score',
                'segment', 'analyzed_at',
            ], $snapshot);
        });
    }
}
