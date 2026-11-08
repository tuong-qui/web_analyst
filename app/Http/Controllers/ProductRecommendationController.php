<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRecommendationRequest;
use App\Services\AssociationRules;
use App\Services\DatasetContext;
use App\Services\ProductRecommendations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductRecommendationController extends Controller
{
    public function index(ProductRecommendationRequest $request, DatasetContext $context, AssociationRules $rules, ProductRecommendations $service): View|RedirectResponse
    {
        $datasets = DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())
            ->orderBy('name')->get(['dataset_id', 'name']);
        if ($datasets->isEmpty()) {
            return to_route('data');
        }

        $datasetId = $context->resolve($request);
        $available = DB::query()->fromSub($rules->baskets($datasetId), 'basket')
            ->select('item')->distinct()->orderBy('item')->pluck('item');
        $defaults = ['support' => 0.1, 'confidence' => 20, 'lift' => 1];
        if ($request->isMethod('post')) {
            $items = $request->validated('items', []);
            if (collect($items)->diff($available)->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'Hãy chọn sản phẩm trong bộ dữ liệu hiện tại.']);
            }
            $filters = array_merge($defaults, $request->safe()->only(['support', 'confidence', 'lift']));
            $service->analyze($datasetId, $filters, $items, $rules);

            return to_route('basket.recommendations', ['dataset_id' => $datasetId]);
        }

        $savedContext = DB::table('datasets')->where('dataset_id', $datasetId)->value('recommendation_context');
        $savedContext = $savedContext ? json_decode($savedContext, true) : [];
        $items = old('items', $savedContext['items'] ?? []);
        $filters = array_merge($defaults, $savedContext['filters'] ?? []);
        $options = $available->merge($items)->unique()->values();
        $recommendations = $service->saved($datasetId);

        return view('product_recommendations', compact('datasets', 'datasetId', 'options', 'items', 'filters', 'recommendations'));
    }
}
