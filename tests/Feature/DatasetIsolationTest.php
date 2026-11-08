<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\DatasetTestCase;

class DatasetIsolationTest extends DatasetTestCase
{
    public function test_orders_displays_the_selected_dataset_name_safely(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => '<script>Shop B</script>']);

        $this->get(route('orders', ['dataset_id' => 2]))->assertOk()
            ->assertViewHas('datasetName', '<script>Shop B</script>')
            ->assertSee('&lt;script&gt;Shop B&lt;/script&gt;', false)
            ->assertDontSee('<script>Shop B</script>', false);
        $this->get(route('orders'))->assertOk()->assertViewHas('datasetName', '<script>Shop B</script>');
        $this->get(route('orders', ['dataset_id' => 1]))->assertOk()->assertViewHas('datasetName', 'Shop A');
    }

    private function customersFile(string $id = 'shared'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('customers.csv', "customer_id,customer_unique_id,customer_zip_code_prefix,customer_city,customer_state\n{$id},unique,,,\n");
    }

    public function test_guests_cannot_read_or_import_data(): void
    {
        $this->actingAsGuest();
        foreach (['customers', 'orders', 'data'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        foreach (['customers.import', 'orders.import', 'datasets.store', 'datasets.select'] as $route) {
            $this->post(route($route))->assertRedirect(route('login'));
        }
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_owner_can_create_and_select_a_dataset_without_spoofing_owner(): void
    {
        $this->post(route('datasets.store'), ['name' => 'New shop', 'user_id' => 2])
            ->assertRedirect(route('data'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('datasets', ['name' => 'New shop', 'user_id' => 1]);
        $id = DB::table('datasets')->where('name', 'New shop')->value('dataset_id');
        $this->post(route('datasets.select'), ['dataset_id' => $id])->assertRedirect(route('customers'));
        $this->post(route('customers.import'), ['file' => $this->customersFile()])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['dataset_id' => $id, 'customer_id' => 'shared']);
    }

    public function test_other_users_dataset_is_rejected_for_read_select_and_import(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Private shop']);
        foreach (['customers', 'orders'] as $route) {
            $this->get(route($route, ['dataset_id' => 2]))->assertNotFound();
        }
        $this->post(route('datasets.select'), ['dataset_id' => 2])->assertNotFound();
        $this->post(route('customers.import'), ['dataset_id' => 2, 'file' => $this->customersFile()])->assertNotFound();
        $this->post(route('orders.import'), ['dataset_id' => 2, 'file' => $this->customersFile()])->assertNotFound();
        $this->get(route('data'))->assertDontSee('Private shop');
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_users_can_import_identical_customer_and_order_ids_without_mixing_previews(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Shop B']);
        foreach ([1, 2] as $id) {
            $this->actingAs(UserFactory::new()->make(['user_id' => $id]));
            $this->post(route('customers.import'), ['dataset_id' => $id, 'file' => $this->customersFile()])->assertSessionHasNoErrors();
            $order = UploadedFile::fake()->createWithContent('orders.csv', "order_id,customer_id,order_status,order_purchase_timestamp,order_approved_at,order_delivered_carrier_date,order_delivered_customer_date,order_estimated_delivery_date\nshared,shared,shop-{$id},,,,,\n");
            $this->post(route('orders.import'), ['dataset_id' => $id, 'file' => $order])->assertSessionHasNoErrors();
        }
        $this->get(route('orders'))->assertSee('shop-2')->assertDontSee('shop-1');
        $this->assertDatabaseCount('customers', 2);
        $this->assertDatabaseCount('orders', 2);
    }

    public function test_customer_in_another_dataset_cannot_be_used_by_an_order(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Shop B']);
        DB::table('customers')->insert(['dataset_id' => 2, 'customer_id' => 'foreign-customer']);
        $file = UploadedFile::fake()->createWithContent('orders.csv', "order_id,customer_id,order_status,order_purchase_timestamp,order_approved_at,order_delivered_carrier_date,order_delivered_customer_date,order_estimated_delivery_date\norder-1,foreign-customer,created,,,,,\n");
        $this->post(route('orders.import'), ['dataset_id' => 1, 'file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_legacy_data_is_invisible_and_cannot_be_selected(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 3, 'user_id' => null, 'name' => 'Legacy']);
        DB::table('customers')->insert(['dataset_id' => 3, 'customer_id' => 'legacy-customer']);
        $this->get(route('customers'))->assertDontSee('legacy-customer');
        $this->post(route('datasets.select'), ['dataset_id' => 3])->assertNotFound();
    }

    public function test_new_user_is_directed_to_create_a_dataset(): void
    {
        $this->actingAs(UserFactory::new()->make(['user_id' => 99]));
        $this->get(route('customers'))->assertRedirect(route('data'));
        $this->get(route('orders'))->assertRedirect(route('data'));
        $this->post(route('customers.import'), ['file' => $this->customersFile()])->assertStatus(422);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_one_user_can_keep_multiple_datasets_separate_and_file_cannot_override_dataset(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Second dataset']);
        $this->post(route('customers.import'), ['dataset_id' => 1, 'file' => $this->customersFile('first-customer')])->assertSessionHasNoErrors();
        $file = UploadedFile::fake()->createWithContent('customers.csv', "customer_id,customer_unique_id,customer_zip_code_prefix,customer_city,customer_state,dataset_id\nsecond-customer,unique,,,,1\n");
        $this->post(route('customers.import'), ['dataset_id' => 2, 'file' => $file])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['dataset_id' => 2, 'customer_id' => 'second-customer']);
        $this->get(route('customers'))->assertSee('second-customer')->assertDontSee('first-customer');
        $this->get(route('customers', ['dataset_id' => 1]))->assertSee('first-customer')->assertDontSee('second-customer');
    }
}
