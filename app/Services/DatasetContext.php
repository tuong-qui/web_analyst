<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatasetContext
{
    public function resolve(Request $request): int
    {
        $request->validate(['dataset_id' => ['nullable', 'integer', 'min:1']]);
        $id = $request->input('dataset_id', $request->session()->get('dataset_id'));
        $query = DB::table('datasets')->where('user_id', $request->user()->getAuthIdentifier());
        if ($id !== null) {
            abort_unless((clone $query)->where('dataset_id', $id)->exists(), 404);
            $request->session()->put('dataset_id', (int) $id);

            return (int) $id;
        }

        $id = $query->orderBy('dataset_id')->value('dataset_id');
        abort_if($id === null, 422, 'Hãy tạo bộ dữ liệu tại mục Dữ liệu trước khi nhập file.');
        $request->session()->put('dataset_id', (int) $id);

        return (int) $id;
    }
}
