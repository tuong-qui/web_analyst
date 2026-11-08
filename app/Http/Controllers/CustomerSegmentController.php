<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerSegmentRequest;
use App\Services\CustomerSegments;
use App\Services\CustomerSegmentSnapshots;
use App\Services\DatasetContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerSegmentController extends Controller
{
    public function index(CustomerSegmentRequest $request, DatasetContext $context, CustomerSegments $service, CustomerSegmentSnapshots $snapshots): View|RedirectResponse
    {
        $datasets = DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())->orderBy('name')->get(['dataset_id', 'name']);
        if ($datasets->isEmpty()) {
            return to_route('data');
        }
        $datasetId = $context->resolve($request);
        $analysis = $service->analyze($datasetId, $request->validated('date'));
        $snapshots->save($datasetId, $analysis['date'], $analysis['customers']);
        $filters = ['segment' => $request->validated('segment') ?? '', 'search' => $request->validated('search') ?? '', 'sort' => $request->validated('sort') ?? 'monetary'];
        $rows = clone $analysis['customers'];
        if ($filters['segment']) {
            $rows->where('segment', $filters['segment']);
        }
        if ($filters['search'] !== '') {
            $rows->whereRaw('INSTR(LOWER(customer), ?) > 0', [mb_strtolower($filters['search'])]);
        }
        $sort = $filters['sort'];
        $page = (int) $request->validated('page', 1);
        $total = $filters['search'] !== '' ? (clone $rows)->count()
            : ($filters['segment'] ? $analysis['groups'][$filters['segment']]['count'] : $analysis['total']);
        $pageRows = $rows->orderBy($sort, $sort === 'recency' ? 'asc' : 'desc')->orderBy('customer')
            ->forPage($page, 25)->get()->map(fn ($row) => (array) $row);
        $customers = new LengthAwarePaginator($pageRows, $total, 25, $page,
            ['path' => $request->url(), 'query' => array_merge($request->query(), ['dataset_id' => $datasetId, 'date' => $analysis['date']])]);

        return view('customer_segments', compact('datasets', 'datasetId', 'analysis', 'filters', 'customers'));
    }
}
