<?php

namespace App\Http\Controllers;

use App\Services\DatasetContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DatasetController extends Controller
{
    public function index(Request $request): View
    {
        $datasets = DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier())->orderBy('dataset_id')->get();

        return view('datasets', compact('datasets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150']]);
        $id = DB::table('datasets')->insertGetId([
            'user_id' => $request->user()->getAuthIdentifier(),
            'name' => $data['name'],
            'created_at' => now(),
            'updated_at' => now(),
        ], 'dataset_id');
        $request->session()->put('dataset_id', $id);

        return to_route('data')->with('success', 'Đã tạo và chọn bộ dữ liệu.');
    }

    public function select(Request $request, DatasetContext $context): RedirectResponse
    {
        $request->validate(['dataset_id' => ['required', 'integer', 'min:1']]);
        $context->resolve($request);

        return to_route('customers');
    }
}
