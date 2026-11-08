<?php

namespace App\Http\Controllers;

use App\Services\DatasetContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public const COLUMNS = [
        'product_id', 'product_category_name', 'product_name_length',
        'product_description_length', 'product_photos_qty', 'product_weight_g',
        'product_length_cm', 'product_height_cm', 'product_width_cm',
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
        $products = DB::table('products')->where('dataset_id', $datasetId)
            ->select($columns)->orderBy('product_id')->limit(100)->get();

        $summary = DB::table('products')->where('dataset_id', $datasetId)
            ->selectRaw("COUNT(*) as total, COUNT(DISTINCT NULLIF(TRIM(product_category_name), '')) as categories, COALESCE(SUM(product_photos_qty), 0) as photos")->first();

        return view('products', compact('products', 'columns', 'datasetId', 'datasetName', 'summary'));
    }
}
