<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRecommendations
{
    public function analyze(int $datasetId, array $filters, array $items, AssociationRules $rules): array
    {
        return DB::transaction(function () use ($datasetId, $filters, $items, $rules) {
            $dataset = DB::table('datasets')->where('dataset_id', $datasetId)->lockForUpdate()->first();
            abort_unless($dataset, 404);
            $support = (float) $filters['support'];
            $confidence = (float) $filters['confidence'];
            $lift = (float) $filters['lift'];
            sort($items, SORT_STRING);
            $recommendations = $items === [] ? collect()
                : $this->fromRules($rules->calculate($datasetId, $support, $confidence, $lift)['rules'], $items);
            DB::table('bundle_recommendations')->where('dataset_id', $datasetId)->delete();
            DB::table('datasets')->where('dataset_id', $datasetId)->update([
                'recommendation_context' => json_encode(['filters' => $filters, 'items' => $items]),
            ]);
            foreach ($recommendations as $rank => $rule) {
                DB::table('bundle_recommendations')->insert([
                    'dataset_id' => $datasetId, 'user_id' => $dataset->user_id,
                    'main_category_name' => $rule['antecedent'], 'related_category_name' => $rule['consequent'],
                    'pair_count' => $rule['count'], 'support' => $rule['support'],
                    'confidence' => $rule['confidence'], 'lift' => $rule['lift'],
                    'rank' => $rank + 1, 'status' => 'active', 'created_at' => now(),
                ]);
            }

            return ['recommendations' => $recommendations];
        });
    }

    public function saved(int $datasetId): Collection
    {
        return DB::table('bundle_recommendations')->where('dataset_id', $datasetId)
            ->orderBy('rank')->orderBy('bundle_id')->get()->map(fn ($row) => [
                'antecedent' => $row->main_category_name, 'consequent' => $row->related_category_name,
                'count' => (int) $row->pair_count, 'support' => (float) $row->support,
                'confidence' => (float) $row->confidence, 'lift' => (float) $row->lift,
            ]);
    }

    public function fromRules(Collection $rules, array $items): Collection
    {
        // Rules already arrive ordered by lift, confidence and co-purchase count.
        return $rules->filter(fn (array $rule) => in_array($rule['antecedent'], $items, true)
                && ! in_array($rule['consequent'], $items, true))
            ->unique('consequent')->take(10)->values();
    }
}
