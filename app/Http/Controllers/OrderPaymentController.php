<?php

namespace App\Http\Controllers;

use App\Services\DatasetContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderPaymentController extends Controller
{
    public const COLUMNS = [
        'order_id', 'payment_sequential', 'payment_type', 'payment_installments', 'payment_value',
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
        $orderPayments = DB::table('order_payments')->where('dataset_id', $datasetId)
            ->select($columns)->orderBy('order_id')->orderBy('payment_sequential')->limit(100)->get();

        $summary = DB::table('order_payments')->where('dataset_id', $datasetId)
            ->selectRaw('COALESCE(SUM(payment_value), 0) as total, COUNT(*) as payments, COUNT(DISTINCT order_id) as orders')->first();

        return view('order_payments', compact('orderPayments', 'columns', 'datasetId', 'datasetName', 'summary'));
    }
}
