<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssociationRules
{
    public function analyze(int $datasetId, float $support, float $confidence, float $lift): array
    {
        return DB::transaction(function () use ($datasetId, $support, $confidence, $lift) {
            // Serialize generation and imports for this dataset, including empty results.
            $dataset = DB::table('datasets')->where('dataset_id', $datasetId)->lockForUpdate()->first();
            abort_unless($dataset, 404);
            $analysis = $this->calculate($datasetId, $support, $confidence, $lift);
            DB::table('association_rules')->where('dataset_id', $datasetId)->delete();
            DB::table('datasets')->where('dataset_id', $datasetId)->update([
                'rules_filters' => json_encode(compact('support', 'confidence', 'lift')),
            ]);
            foreach ($analysis['rules']->chunk(250) as $chunk) {
                DB::table('association_rules')->insert($chunk->map(fn ($rule) => [
                    'dataset_id' => $datasetId, 'user_id' => $dataset->user_id,
                    'antecedent_category_name' => $rule['antecedent'], 'consequent_category_name' => $rule['consequent'],
                    'pair_count' => $rule['count'], 'support' => $rule['support'],
                    'confidence' => $rule['confidence'], 'lift' => $rule['lift'], 'created_at' => now(),
                ])->all());
            }

            return $analysis;
        });
    }

    public function saved(int $datasetId): array
    {
        $baskets = $this->baskets($datasetId);
        $rules = DB::table('association_rules')->where('dataset_id', $datasetId)
            ->orderByDesc('lift')->orderByDesc('confidence')->orderByDesc('pair_count')
            ->orderBy('antecedent_category_name')->orderBy('consequent_category_name')
            ->get()->map(fn ($rule) => [
                'antecedent' => $rule->antecedent_category_name, 'consequent' => $rule->consequent_category_name,
                'count' => (int) $rule->pair_count, 'support' => (float) $rule->support,
                'confidence' => (float) $rule->confidence, 'lift' => (float) $rule->lift,
            ]);

        return [
            'baskets' => DB::query()->fromSub(clone $baskets, 'b')->distinct()->count('order_id'),
            'items' => DB::query()->fromSub($baskets, 'b')->distinct()->count('item'),
            'rules' => $rules,
        ];
    }

    public static function invalidate(int $datasetId): void
    {
        DB::table('datasets')->where('dataset_id', $datasetId)->increment('rules_version');
        DB::table('bundle_recommendations')->where('dataset_id', $datasetId)->delete();
        DB::table('association_rules')->where('dataset_id', $datasetId)->delete();
        DB::table('datasets')->where('dataset_id', $datasetId)->update(['rules_filters' => null, 'recommendation_context' => null]);
    }

    public function calculate(int $datasetId, float $support, float $confidence, float $lift): array
    {
        $baskets = $this->baskets($datasetId);

        $total = DB::query()->fromSub(clone $baskets, 'b')->distinct()->count('order_id');
        $counts = DB::query()->fromSub(clone $baskets, 'b')
            ->select('item')->selectRaw('COUNT(*) as total')->groupBy('item')->pluck('total', 'item');

        return $this->rules($baskets, $total, $counts, $support, $confidence, $lift);
    }

    public function baskets(int $datasetId): Builder
    {
        $column = 'p.product_category_name';

        return DB::table('order_items as i')
            ->join('orders as o', function ($join) {
                $join->on('o.dataset_id', '=', 'i.dataset_id')->on('o.order_id', '=', 'i.order_id');
            })
            ->leftJoin('products as p', function ($join) {
                $join->on('p.dataset_id', '=', 'i.dataset_id')->on('p.product_id', '=', 'i.product_id');
            })
            ->where('i.dataset_id', $datasetId)
            ->where(function ($query) {
                $query->whereNull('o.order_status')->orWhereNotIn('o.order_status', ['canceled', 'unavailable']);
            })
            ->whereNotNull($column)->whereRaw("TRIM({$column}) <> ''")
            ->select('i.order_id')->selectRaw("TRIM({$column}) as item")->distinct();

    }

    private function rules(Builder $baskets, int $total, Collection $counts, float $support, float $confidence, float $lift): array
    {
        $rules = collect();
        if ($total > 0) {
            $pairs = DB::query()->fromSub(clone $baskets, 'a')
                ->joinSub(clone $baskets, 'b', function ($join) {
                    $join->on('a.order_id', '=', 'b.order_id')->on('a.item', '<', 'b.item');
                })
                ->select('a.item as antecedent', 'b.item as consequent')->selectRaw('COUNT(*) as total')
                ->groupBy('a.item', 'b.item')->havingRaw('COUNT(*) >= ?', [max(1, (int) ceil($support / 100 * $total - 1e-10))]);

            foreach ($pairs->cursor() as $pair) {
                foreach ([[$pair->antecedent, $pair->consequent], [$pair->consequent, $pair->antecedent]] as [$a, $b]) {
                    $ruleConfidence = $pair->total / $counts[$a] * 100;
                    $ruleLift = ($pair->total / $counts[$a]) / ($counts[$b] / $total);
                    if ($ruleConfidence + 1e-10 >= $confidence && $ruleLift + 1e-10 >= $lift) {
                        $rules->push([
                            'antecedent' => $a, 'consequent' => $b, 'count' => (int) $pair->total,
                            'support' => $pair->total / $total * 100,
                            'confidence' => $ruleConfidence, 'lift' => $ruleLift,
                        ]);
                    }
                }
            }
        }
        $rules = $rules->sort(function ($a, $b) {
            return ($b['lift'] <=> $a['lift']) ?: ($b['confidence'] <=> $a['confidence'])
                ?: ($b['count'] <=> $a['count']) ?: strcmp($a['antecedent'], $b['antecedent'])
                ?: strcmp($a['consequent'], $b['consequent']);
        })->values();

        return ['baskets' => $total, 'items' => $counts->count(), 'rules' => $rules];
    }
}
