<?php

namespace App\Http\Controllers;

use App\Services\AssociationRules;
use App\Services\DatasetContext;
use App\Services\OrderFileReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductImportController extends Controller
{
    public function store(Request $request, OrderFileReader $reader, DatasetContext $context): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,tsv,xlsx,xls,ods', 'max:51200'],
        ]);
        $datasetId = $context->resolve($request);
        set_time_limit(300);

        $columns = ProductController::COLUMNS;
        $rows = $reader->rows($request->file('file'));

        try {
            $header = $rows->current();
            if ($header === null) {
                throw ValidationException::withMessages(['file' => 'File rỗng.']);
            }

            $header = array_map(fn ($value) => trim($value ?? ''), $header);
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $aliases = [
                'product_name_lenght' => 'product_name_length',
                'product_description_lenght' => 'product_description_length',
            ];
            $header = array_map(fn ($column) => $aliases[$column] ?? $column, $header);
            if (count(array_unique($header)) !== count($header) || array_diff($columns, $header)) {
                throw ValidationException::withMessages([
                    'file' => 'File phải có đủ 9 cột: '.implode(', ', $columns).'.',
                ]);
            }

            $count = DB::transaction(function () use ($rows, $header, $columns, $datasetId) {
                AssociationRules::invalidate($datasetId);
                $count = 0;
                $line = 1;
                $batch = [];
                $batchLines = [];
                $validator = Validator::make([], [
                    'product_id' => ['required', 'string', 'max:50'],
                    'product_category_name' => ['nullable', 'string', 'max:100'],
                    'product_name_length' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                    'product_description_length' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                    'product_photos_qty' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                    'product_weight_g' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                    'product_length_cm' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                    'product_height_cm' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                    'product_width_cm' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
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
                    foreach (['product_name_length', 'product_description_length', 'product_photos_qty'] as $column) {
                        if (preg_match('/^\d+\.0+$/', $data[$column] ?? '')) {
                            $data[$column] = preg_replace('/\.0+$/', '', $data[$column]);
                        }
                    }
                    $validator->setData($data);
                    if ($validator->fails()) {
                        throw ValidationException::withMessages([
                            'file' => "Dòng {$line}: ".$validator->errors()->first(),
                        ]);
                    }

                    $id = $data['product_id'];
                    if (isset($batchLines[$id])) {
                        throw ValidationException::withMessages(['file' => "Dòng {$line}: product_id trùng với dòng {$batchLines[$id]}."]);
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

        return to_route('products')->with('success', "Đã lưu {$count} sản phẩm.");
    }

    private function insertBatch(array $batch, array $lines, int $datasetId): void
    {
        $existing = DB::table('products')->where('dataset_id', $datasetId)->whereIn('product_id', array_column($batch, 'product_id'))->pluck('product_id');
        if ($existing->isNotEmpty()) {
            $line = $lines[$existing->first()];
            throw ValidationException::withMessages(['file' => "Dòng {$line}: product_id đã tồn tại."]);
        }

        DB::table('products')->insert($batch);
    }
}
