<?php

namespace App\Http\Controllers;

use App\Services\DatasetContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderItemController extends Controller
{
    public const COLUMNS = [
        'order_id', 'order_item_id', 'product_id', 'shipping_limit_date', 'price', 'freight_value',
    ];

    public function index(Request $request, DatasetContext $context): View|RedirectResponse
    {
        if (! DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())->exists()) {
            return to_route('data');
        }

        $datasetId = $context->resolve($request);
        $datasetName = DB::table('datasets')->where('dataset_id', $datasetId)
            ->where('user_id', $request->user()->getAuthIdentifier())->value('name');
        $columns = self::COLUMNS;
        $orderItems = DB::table('order_items')->where('dataset_id', $datasetId)
            ->select($columns)->orderBy('order_id')->orderBy('order_item_id')->limit(100)->get();

        $summary = DB::table('order_items')->where('dataset_id', $datasetId)
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT order_id) as orders, COALESCE(SUM(price), 0) as value')->first();

        return view('order_items', compact('orderItems', 'columns', 'datasetId', 'datasetName', 'summary'));
    }
}
