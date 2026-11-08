<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DatasetTestCase;

class HomeAnalyticsTest extends DatasetTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['order_payments' => ['order_id', 'payment_type', 'payment_value'], 'products' => ['product_id', 'product_category_name'], 'order_items' => ['order_id', 'product_id', 'price']] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns) {
                $table->unsignedBigInteger('dataset_id');
                foreach ($columns as $column) {
                    $table->string($column)->nullable();
                }
            });
        }
    }

    public function test_home_aggregates_payments_without_multiplying_orders_or_item_values(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Other shop']);
        foreach ([1, 2] as $dataset) {
            DB::table('customers')->insert(['dataset_id' => $dataset, 'customer_id' => 'c', 'customer_unique_id' => 'person']);
            DB::table('orders')->insert(['dataset_id' => $dataset, 'order_id' => 'o', 'customer_id' => 'c', 'order_status' => 'delivered', 'order_purchase_timestamp' => '2026-01-05 10:00:00']);
            DB::table('products')->insert(['dataset_id' => $dataset, 'product_id' => 'p', 'product_category_name' => 'books']);
            DB::table('order_items')->insert(['dataset_id' => $dataset, 'order_id' => 'o', 'product_id' => 'p', 'price' => 75]);
            DB::table('order_payments')->insert([
                ['dataset_id' => $dataset, 'order_id' => 'o', 'payment_type' => 'credit_card', 'payment_value' => 100],
                ['dataset_id' => $dataset, 'order_id' => 'o', 'payment_type' => 'voucher', 'payment_value' => 20],
            ]);
        }
        DB::table('orders')->insert(['dataset_id' => 1, 'order_id' => 'undated', 'customer_id' => 'c', 'order_status' => 'canceled']);

        $response = $this->get(route('home', ['dataset_id' => 1]))->assertOk();
        $data = $response->viewData('analytics');
        $this->assertSame(2, $data['orders']);
        $this->assertSame(120.0, $data['payments']);
        $this->assertSame(120.0, $data['average']);
        $this->assertEquals(1, $data['customers']);
        $this->assertSame(50.0, $data['deliveryRate']);
        $this->assertSame(1, $data['undatedOrders']);
        $this->assertEquals(1, $data['monthly']->first()->orders);
        $this->assertEquals(120, $data['monthly']->first()->value);
        $this->assertEquals(75, $data['categories']->first()->value);
        $response->assertSessionHas('dataset_id', 1)->assertSee('Thanh toán theo tháng');
        $this->get(route('home', ['dataset_id' => 2]))->assertOk()->assertViewHas('datasetId', 2);
    }

    public function test_home_rejects_another_users_dataset(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 99, 'name' => 'Private']);

        $this->get(route('home', ['dataset_id' => 2]))->assertNotFound();
    }

    public function test_empty_dataset_renders_zero_metrics(): void
    {
        $response = $this->get(route('home'))->assertOk()->assertSee('Chưa có đơn hàng');

        $this->assertSame(0, $response->viewData('analytics')['orders']);
        $this->assertSame(0, $response->viewData('analytics')['average']);
    }

    public function test_user_without_datasets_sees_onboarding(): void
    {
        DB::table('datasets')->delete();

        $this->get(route('home'))->assertOk()->assertSee('Tạo bộ dữ liệu')->assertViewMissing('analytics');
    }

    public function test_guest_sees_login_without_private_analytics(): void
    {
        auth()->forgetGuards();

        $this->get(route('home'))->assertOk()->assertSee('Đăng nhập')->assertViewMissing('analytics');
    }
}
