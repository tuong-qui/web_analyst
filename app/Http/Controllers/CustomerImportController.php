<?php

namespace App\Http\Controllers;

use App\Services\DatasetContext;
use App\Services\OrderFileReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerImportController extends Controller
{
    public function store(Request $request, OrderFileReader $reader, DatasetContext $context): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,tsv,xlsx,xls,ods', 'max:51200'],
        ]);
        $datasetId = $context->resolve($request);
        set_time_limit(300);

        $columns = [
            'customer_id', 'customer_unique_id', 'customer_zip_code_prefix',
            'customer_city', 'customer_state',
        ];
        $rows = $reader->rows($request->file('file'));

        try {
            $header = $rows->current();
            if ($header === null) {
                throw ValidationException::withMessages(['file' => 'File rỗng.']);
            }

            $header = array_map(fn ($value) => trim($value ?? ''), $header);
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            if (count(array_unique($header)) !== count($header) || array_diff($columns, $header)) {
                throw ValidationException::withMessages([
                    'file' => 'File phải có đủ 5 cột: '.implode(', ', $columns).'.',
                ]);
            }

            $count = DB::transaction(function () use ($rows, $header, $columns, $datasetId) {
                $count = 0;
                $line = 1;
                $batch = [];
                $batchLines = [];
                $validator = Validator::make([], [
                    'customer_id' => ['required', 'string', 'max:50'],
                    'customer_unique_id' => ['required', 'string', 'max:50'],
                    'customer_zip_code_prefix' => ['nullable', 'integer', 'min:0', 'max:99999'],
                    'customer_city' => ['nullable', 'string', 'max:100'],
                    'customer_state' => ['nullable', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
                ]);
                $rows->next();
                for (; $rows->valid(); $rows->next()) {
                    $row = $rows->current();
                    $line++;
                    if (count(array_filter($row, fn ($value) => $value !== null && trim($value) !== '')) === 0) {
                        continue;
                    }
                    if (count($row) !== count($header)) {
                        throw ValidationException::withMessages(['file' => "Dòng {$line}: số cột không khớp tiêu đề."]);
                    }

                    $data = array_intersect_key(array_combine($header, $row), array_flip($columns));
                    $data = array_map(fn ($value) => trim($value ?? '') === '' ? null : trim($value), $data);
                    if (preg_match('/^\d{1,5}$/', $data['customer_zip_code_prefix'] ?? '')) {
                        $data['customer_zip_code_prefix'] = (string) (int) $data['customer_zip_code_prefix'];
                    }
                    $validator->setData($data);
                    if ($validator->fails()) {
                        throw ValidationException::withMessages([
                            'file' => "Dòng {$line}: ".$validator->errors()->first(),
                        ]);
                    }

                    $id = $data['customer_id'];
                    if (isset($batchLines[$id])) {
                        throw ValidationException::withMessages(['file' => "Dòng {$line}: customer_id trùng với dòng {$batchLines[$id]}."]);
                    }
                    $data['dataset_id'] = $datasetId;
                    $batch[] = $data;
                    $batchLines[$id] = $line;
                    $count++;
                    if (count($batch) === 500) {
                        $this->insertBatch($batch, $batchLines, $datasetId);
                        $batch = [];
                        $batchLines = [];
                    }
                }
                if ($batch !== []) {
                    $this->insertBatch($batch, $batchLines, $datasetId);
                }

                if ($count === 0) {
                    throw ValidationException::withMessages(['file' => 'File không có dòng dữ liệu.']);
                }

                return $count;
            });
        } finally {
            unset($rows);
        }

        return to_route('customers')->with('success', "Đã lưu {$count} khách hàng.");
    }

    private function insertBatch(array $batch, array $lines, int $datasetId): void
    {
        $existing = DB::table('customers')->where('dataset_id', $datasetId)->whereIn('customer_id', array_column($batch, 'customer_id'))->pluck('customer_id');
        if ($existing->isNotEmpty()) {
            $line = $lines[$existing->first()];
            throw ValidationException::withMessages(['file' => "Dòng {$line}: customer_id đã tồn tại."]);
        }

        DB::table('customers')->insert($batch);
    }
}
