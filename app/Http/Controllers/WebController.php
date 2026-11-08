<?php

namespace App\Http\Controllers;

use App\Services\DatasetContext;
use App\Services\HomeAnalytics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebController extends Controller
{
    public function index(Request $request, DatasetContext $context): View
    {
        if (! $request->user()) {
            return view('home', ['datasets' => collect()]);
        }

        $datasets = DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())
            ->orderBy('name')->get(['dataset_id', 'name']);
        if ($datasets->isEmpty()) {
            return view('home', compact('datasets'));
        }

        $datasetId = $context->resolve($request);
        $datasetName = $datasets->firstWhere('dataset_id', $datasetId)->name;
        $analytics = app(HomeAnalytics::class)->forDataset($datasetId);

        return view('home', compact('datasets', 'datasetId', 'datasetName', 'analytics'));
    }

    public function orders(Request $request, DatasetContext $context): View|RedirectResponse
    {
        if (! DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())->exists()) {
            return to_route('data');
        }
        $datasetId = $context->resolve($request);
        $datasetName = DB::table('datasets')->where('dataset_id', $datasetId)
            ->where('user_id', $request->user()->getAuthIdentifier())->value('name');
        $columns = [
            'order_id', 'customer_id', 'order_status', 'order_purchase_timestamp',
            'order_approved_at', 'order_delivered_carrier_date',
            'order_delivered_customer_date', 'order_estimated_delivery_date',
        ];
        $orders = DB::table('orders')->where('dataset_id', $datasetId)->select($columns)->orderBy('order_id')->limit(100)->get();

        $summary = DB::table('orders')->where('dataset_id', $datasetId)
            ->selectRaw("COUNT(*) as total, COUNT(DISTINCT customer_id) as customers, COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered")->first();

        return view('orders', compact('orders', 'columns', 'datasetId', 'datasetName', 'summary'));
    }

    public function customers(Request $request, DatasetContext $context): View|RedirectResponse
    {
        if (! DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())->exists()) {
            return to_route('data');
        }
        $datasetId = $context->resolve($request);
        $columns = ['customer_id', 'customer_unique_id', 'customer_zip_code_prefix', 'customer_city', 'customer_state'];
        $customers = DB::table('customers')->where('dataset_id', $datasetId)->select($columns)->orderBy('customer_id')->limit(100)->get();
        $datasetName = DB::table('datasets')->where('dataset_id', $datasetId)
            ->where('user_id', $request->user()->getAuthIdentifier())->value('name');

        $summary = DB::table('customers')->where('dataset_id', $datasetId)
            ->selectRaw("COUNT(*) as total, COUNT(DISTINCT NULLIF(TRIM(customer_unique_id), '')) as unique_customers, COUNT(DISTINCT NULLIF(TRIM(customer_state), '')) as states")->first();

        return view('customers', compact('customers', 'columns', 'datasetId', 'datasetName', 'summary'));
    }
}
