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

class OrderItemImportController extends Controller
{
    public function store(Request $request, OrderFileReader $reader, DatasetContext $context): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,tsv,xlsx,xls,ods', 'max:51200'],
        ]);
        $datasetId = $context->resolve($request);
        set_time_limit(300);

        $columns = OrderItemController::COLUMNS;
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
                    'file' => 'File phải có đủ 6 cột: '.implode(', ', $columns).'.',
                ]);
            }

            $count = DB::transaction(function () use ($rows, $header, $columns, $datasetId) {
                AssociationRules::invalidate($datasetId);
                $count = 0;
                $line = 1;
                $batch = [];
                $batchLines = [];
                $validator = Validator::make([], [
                    'order_id' => ['required', 'string', 'max:50'],
                    'order_item_id' => ['required', 'integer', 'min:1', 'max:2147483647'],
                    'product_id' => ['nullable', 'string', 'max:50'],
                    'shipping_limit_date' => ['nullable', 'date_format:Y-m-d H:i:s'],
                    'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                    'freight_value' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
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

                    $data['order_item_id'] = (int) $data['order_item_id'];
                    $id = $data['order_id'].':'.$data['order_item_id'];
                    if (isset($batchLines[$id])) {
                        throw ValidationException::withMessages(['file' => "Dòng {$line}: cặp order_id và order_item_id trùng với dòng {$batchLines[$id]}."]);
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

        return to_route('order_items')->with('success', "Đã lưu {$count} chi tiết đơn hàng.");
    }

    private function insertBatch(array $batch, array $lines, int $datasetId): void
    {
        $existing = DB::table('order_items')->where('dataset_id', $datasetId)
            ->whereIn('order_id', array_unique(array_column($batch, 'order_id')))
            ->get(['order_id', 'order_item_id'])
            ->mapWithKeys(fn ($item) => [$item->order_id.':'.$item->order_item_id => true]);
        $orders = DB::table('orders')->where('dataset_id', $datasetId)
            ->whereIn('order_id', array_unique(array_column($batch, 'order_id')))->pluck('order_id')->flip();
        $products = DB::table('products')->where('dataset_id', $datasetId)
            ->whereIn('product_id', array_filter(array_unique(array_column($batch, 'product_id'))))->pluck('product_id')->flip();
        foreach ($batch as $data) {
            $key = $data['order_id'].':'.$data['order_item_id'];
            $line = $lines[$key];
            if ($existing->has($key)) {
                throw ValidationException::withMessages(['file' => "Dòng {$line}: cặp order_id và order_item_id đã tồn tại."]);
            }
            if (! $orders->has($data['order_id'])) {
                throw ValidationException::withMessages(['file' => "Dòng {$line}: order_id chưa tồn tại trong bộ dữ liệu. Hãy nhập đơn hàng trước."]);
            }
            if ($data['product_id'] !== null && ! $products->has($data['product_id'])) {
                throw ValidationException::withMessages(['file' => "Dòng {$line}: product_id chưa tồn tại trong bộ dữ liệu. Hãy nhập sản phẩm trước."]);
            }
        }

        DB::table('order_items')->insert($batch);
    }
}
