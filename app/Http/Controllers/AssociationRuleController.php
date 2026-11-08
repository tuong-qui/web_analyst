<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssociationRuleRequest;
use App\Services\AssociationRules;
use App\Services\DatasetContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssociationRuleController extends Controller
{
    public function index(AssociationRuleRequest $request, DatasetContext $context, AssociationRules $service): View|RedirectResponse
    {
        $datasets = DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())
            ->orderBy('name')->get(['dataset_id', 'name']);
        if ($datasets->isEmpty()) {
            return to_route('data');
        }
        $datasetId = $context->resolve($request);
        $defaults = ['support' => 0.1, 'confidence' => 20, 'lift' => 1];
        if ($request->isMethod('post')) {
            $filters = array_merge($defaults, $request->safe()->only(['support', 'confidence', 'lift']));
            $service->analyze($datasetId, (float) $filters['support'], (float) $filters['confidence'], (float) $filters['lift']);

            return to_route('basket.rules', ['dataset_id' => $datasetId]);
        }
        $savedFilters = DB::table('datasets')->where('dataset_id', $datasetId)->value('rules_filters');
        $filters = array_merge($defaults, $savedFilters ? json_decode($savedFilters, true) : []);
        $analysis = $service->saved($datasetId);
        $page = (int) $request->input('page', 1);
        $rules = new LengthAwarePaginator($analysis['rules']->forPage($page, 25)->values(), $analysis['rules']->count(), 25, $page,
            ['path' => $request->url(), 'query' => array_merge($request->query(), ['dataset_id' => $datasetId])]);

        return view('association_rules', compact('datasets', 'datasetId', 'filters', 'analysis', 'rules'));
    }
}
