<?php

namespace Tests\Feature;

use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DatasetTestCase;

class DataSummaryTest extends DatasetTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['products' => ProductController::COLUMNS, 'order_items' => OrderItemController::COLUMNS] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns) {
                $table->unsignedBigInteger('dataset_id');
                foreach ($columns as $column) {
                    $table->string($column)->nullable();
                }
            });
        }
    }

    public function test_summaries_count_all_rows_and_only_the_selected_dataset(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Second shop']);
        foreach ([1 => 101, 2 => 1] as $dataset => $count) {
            for ($i = 0; $i < $count; $i++) {
                DB::table('customers')->insert(['dataset_id' => $dataset, 'customer_id' => "c{$i}", 'customer_unique_id' => 'same-person', 'customer_state' => 'SP']);
                DB::table('orders')->insert(['dataset_id' => $dataset, 'order_id' => "o{$i}", 'customer_id' => 'c0', 'order_status' => 'delivered']);
                DB::table('products')->insert(['dataset_id' => $dataset, 'product_id' => "p{$i}", 'product_category_name' => 'books', 'product_photos_qty' => 2]);
                DB::table('order_items')->insert(['dataset_id' => $dataset, 'order_id' => 'o0', 'order_item_id' => $i + 1, 'price' => '1.50']);
            }
        }

        $expected = [
            'customers' => ['total' => 101, 'unique_customers' => 1, 'states' => 1],
            'orders' => ['total' => 101, 'customers' => 1, 'delivered' => 101],
            'products' => ['total' => 101, 'categories' => 1, 'photos' => 202],
            'order_items' => ['total' => 101, 'orders' => 1, 'value' => 151.5],
        ];
        foreach ($expected as $route => $metrics) {
            $response = $this->get(route($route, ['dataset_id' => 1]))->assertOk();
            foreach ($metrics as $field => $value) {
                $response->assertViewHas('summary', fn ($summary) => (float) $summary->{$field} === (float) $value);
            }
            $this->get(route($route, ['dataset_id' => 2]))->assertOk()
                ->assertViewHas('summary', fn ($summary) => (int) $summary->total === 1);
        }
    }

    public function test_empty_datasets_display_zero_summaries(): void
    {
        foreach (['customers', 'orders', 'products', 'order_items'] as $route) {
            $this->get(route($route))->assertOk()->assertViewHas('summary', function ($summary) {
                foreach ((array) $summary as $value) {
                    if ((float) $value !== 0.0) {
                        return false;
                    }
                }

                return true;
            });
        }
    }
}
