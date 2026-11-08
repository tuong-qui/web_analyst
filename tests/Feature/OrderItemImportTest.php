<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DatasetTestCase;

class OrderItemImportTest extends DatasetTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('dataset_id');
            $table->string('product_id');
            $table->primary(['dataset_id', 'product_id']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('dataset_id');
            $table->string('order_id');
            $table->integer('order_item_id');
            $table->string('product_id')->nullable();
            $table->dateTime('shipping_limit_date')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('freight_value', 10, 2)->nullable();
            $table->primary(['dataset_id', 'order_id', 'order_item_id']);
        });
        DB::table('customers')->insert(['dataset_id' => 1, 'customer_id' => 'c']);
        DB::table('orders')->insert(['dataset_id' => 1, 'order_id' => 'o', 'customer_id' => 'c']);
        DB::table('products')->insert(['dataset_id' => 1, 'product_id' => 'p']);
    }

    private function upload(string $rows)
    {
        return $this->post(route('order_items.import'), ['file' => UploadedFile::fake()->createWithContent('items.csv', "order_id,order_item_id,product_id,shipping_limit_date,price,freight_value\n".$rows)]);
    }

    public function test_import_and_preview(): void
    {
        $this->upload("o,1,p,2026-10-16 12:00:00,12.50,2\no,2,p,,0,0\n")->assertSessionHasNoErrors()->assertRedirect(route('order_items'));
        $this->assertDatabaseCount('order_items', 2);
        $this->get(route('order_items'))->assertOk()->assertViewHas('orderItems', fn ($rows) => $rows->count() === 2)->assertSee('Shop A');
    }

    public function test_invalid_imports_roll_back(): void
    {
        foreach (["o,1,p,,1,0\no,1,p,,2,0\n", "o,1,p,,1,0\nmissing,2,p,,1,0\n", "o,1,p,,1,0\no,2,missing,,1,0\n", "o,1,p,,1,0\no,2,p,invalid,-1,0\n"] as $rows) {
            $this->upload($rows)->assertSessionHasErrors('file');
            $this->assertDatabaseCount('order_items', 0);
        }
    }

    public function test_existing_row_is_preserved(): void
    {
        $this->upload("o,1,p,,1,0\n")->assertSessionHasNoErrors();
        $this->upload("o,2,p,,2,0\no,1,p,,3,0\n")->assertSessionHasErrors('file');
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_other_users_dataset_is_inaccessible(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Private']);
        $this->get(route('order_items', ['dataset_id' => 2]))->assertNotFound();
        $this->post(route('order_items.import'), ['dataset_id' => 2, 'file' => UploadedFile::fake()->createWithContent('items.csv', 'x')])->assertNotFound();
    }

    public function test_guests_require_login(): void
    {
        $this->actingAsGuest();
        $this->get(route('order_items'))->assertRedirect(route('login'));
        $this->post(route('order_items.import'))->assertRedirect(route('login'));
    }
}
