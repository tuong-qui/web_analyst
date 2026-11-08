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

class OrderImportController extends Controller
{
    public function store(Request $request, OrderFileReader $reader, DatasetContext $context): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,tsv,xlsx,xls,ods', 'max:51200'],
        ]);
        $datasetId = $context->resolve($request);
        set_time_limit(300);

        $columns = [
            'order_id', 'customer_id', 'order_status',
            'order_approved_at', 'order_delivered_carrier_date',
            'order_delivered_customer_date', 'order_estimated_delivery_date', 'order_purchase_timestamp',
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
                    'file' => 'File phải có đủ 8 cột: '.implode(', ', $columns).'.',
                ]);
            }

            $count = DB::transaction(function () use ($rows, $header, $columns, $datasetId) {
                AssociationRules::invalidate($datasetId);
                $count = 0;
                $line = 1;
                $batch = [];
                $batchLines = [];
                $rules = [
                    'order_id' => ['required', 'string', 'max:50'],
                    'customer_id' => ['required', 'string', 'max:50'],
                    'order_status' => ['nullable', 'string', 'max:30'],
                ];
                foreach (array_slice($columns, 3) as $column) {
                    $rules[$column] = ['nullable', 'date_format:Y-m-d H:i:s'];
                }
                $validator = Validator::make([], $rules, [
                    '*.date_format' => 'Ngày giờ phải có định dạng YYYY-MM-DD HH:MM:SS.',
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
                    $validator->setData($data);
                    if ($validator->fails()) {
                        throw ValidationException::withMessages([
                            'file' => "Dòng {$line}: ".$validator->errors()->first(),
                        ]);
                    }

                    $id = $data['order_id'];
                    if (isset($batchLines[$id])) {
                        throw ValidationException::withMessages(['file' => "Dòng {$line}: order_id trùng với dòng {$batchLines[$id]}."]);
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

        return to_route('orders')->with('success', "Đã lưu {$count} đơn hàng.");
    }

    private function insertBatch(array $batch, array $lines, int $datasetId): void
    {
        $existing = DB::table('orders')->where('dataset_id', $datasetId)->whereIn('order_id', array_column($batch, 'order_id'))->pluck('order_id');
        if ($existing->isNotEmpty()) {
            $line = $lines[$existing->first()];
            throw ValidationException::withMessages(['file' => "Dòng {$line}: order_id đã tồn tại."]);
        }
        $customers = DB::table('customers')->where('dataset_id', $datasetId)->whereIn('customer_id', array_unique(array_column($batch, 'customer_id')))->pluck('customer_id')->flip();
        foreach ($batch as $data) {
            if (! $customers->has($data['customer_id'])) {
                $line = $lines[$data['order_id']];
                throw ValidationException::withMessages(['file' => "Dòng {$line}: customer_id chưa tồn tại. Hãy nhập dữ liệu customers trước."]);
            }
        }

        DB::table('orders')->insert($batch);
    }
}
