<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\DatasetTestCase;

class OrderPaymentImportTest extends DatasetTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $migration = require database_path('migrations/2026_10_17_071656_create_order_payments_table.php');
        $migration->up();
        $migration->up();
        DB::table('customers')->insert(['dataset_id' => 1, 'customer_id' => 'c']);
        DB::table('orders')->insert(['dataset_id' => 1, 'order_id' => 'o', 'customer_id' => 'c']);
    }

    private function upload(string $rows)
    {
        return $this->post(route('order_payments.import'), ['file' => UploadedFile::fake()->createWithContent('items.csv', "order_id,payment_sequential,payment_type,payment_installments,payment_value\n".$rows)]);
    }

    public function test_import_and_preview(): void
    {
        $this->upload("o,1,credit_card,2,12.50\no,2,voucher,0,2\n")->assertSessionHasNoErrors()->assertRedirect(route('order_payments'));
        $this->assertDatabaseCount('order_payments', 2);
        $this->get(route('order_payments'))->assertOk()->assertViewHas('orderPayments', fn ($rows) => $rows->count() === 2)->assertSee('Shop A')->assertViewHas('summary', fn ($summary) => (float) $summary->total === 14.5 && $summary->orders === 1 && $summary->payments === 2);
    }

    public function test_summary_and_preview_only_include_selected_dataset(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Private']);
        DB::table('customers')->insert(['dataset_id' => 2, 'customer_id' => 'private-customer']);
        DB::table('orders')->insert(['dataset_id' => 2, 'order_id' => 'private', 'customer_id' => 'private-customer']);
        DB::table('order_payments')->insert(['dataset_id' => 2, 'order_id' => 'private', 'payment_sequential' => 1, 'payment_value' => 999]);

        $this->get(route('order_payments'))->assertOk()
            ->assertViewHas('orderPayments', fn ($rows) => $rows->isEmpty())
            ->assertViewHas('summary', fn ($summary) => (float) $summary->total === 0.0 && $summary->payments === 0);
        $this->upload("o,1,credit_card,1,10\n")->assertSessionHasNoErrors();
        $this->get(route('order_payments'))->assertViewHas('summary', fn ($summary) => (float) $summary->total === 10.0);
    }

    public function test_invalid_imports_roll_back(): void
    {
        foreach (["o,1,credit_card,1,1\no,1,credit_card,1,2\n", "o,1,credit_card,1,1\nmissing,2,credit_card,1,1\n", "o,1,credit_card,1,1\no,2,credit_card,-1,1\n", "o,1,credit_card,1,1\no,2,credit_card,1,-1\n"] as $rows) {
            $this->upload($rows)->assertSessionHasErrors('file');
            $this->assertDatabaseCount('order_payments', 0);
        }
    }

    public function test_existing_row_is_preserved(): void
    {
        $this->upload("o,1,credit_card,1,1\n")->assertSessionHasNoErrors();
        $this->upload("o,2,credit_card,1,2\no,1,credit_card,1,3\n")->assertSessionHasErrors('file');
        $this->assertDatabaseCount('order_payments', 1);
    }

    public function test_other_users_dataset_is_inaccessible(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Private']);
        $this->get(route('order_payments', ['dataset_id' => 2]))->assertNotFound();
        $this->post(route('order_payments.import'), ['dataset_id' => 2, 'file' => UploadedFile::fake()->createWithContent('items.csv', 'x')])->assertNotFound();
    }

    public function test_guests_require_login(): void
    {
        $this->actingAsGuest();
        $this->get(route('order_payments'))->assertRedirect(route('login'));
        $this->post(route('order_payments.import'))->assertRedirect(route('login'));
    }
}
