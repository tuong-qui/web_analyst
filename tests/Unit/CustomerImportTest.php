<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\DatasetTestCase;

class CustomerImportTest extends DatasetTestCase
{
    private function csv(string $rows): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('customers.csv', "\xEF\xBB\xBFcustomer_id,customer_unique_id,customer_zip_code_prefix,customer_city,customer_state\n".$rows);
    }

    public function test_import_saves_customers_and_displays_database_preview(): void
    {
        $this->followingRedirects()->post(route('customers.import'), ['file' => $this->csv("customer-1,unique-1,01234,sao paulo,SP\ncustomer-2,unique-1,,,\n")])
            ->assertOk()->assertSessionHasNoErrors()->assertSee('customer-1')->assertSee('sao paulo');
        $this->assertDatabaseCount('customers', 2);
        $this->assertDatabaseHas('customers', ['customer_id' => 'customer-2', 'customer_city' => null]);
    }

    public function test_invalid_row_rolls_back_import(): void
    {
        $this->post(route('customers.import'), ['file' => $this->csv("customer-1,unique-1,12345,city,SP\ncustomer-2,unique-2,invalid,city,SP\n")])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_duplicate_id_rolls_back_import(): void
    {
        $this->post(route('customers.import'), ['file' => $this->csv("customer-1,unique-1,,,\ncustomer-1,unique-2,,,\n")])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_missing_headers_are_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('customers.csv', "customer_id\ncustomer-1\n");
        $this->post(route('customers.import'), ['file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_preview_contains_at_most_100_customers(): void
    {
        for ($i = 1; $i <= 101; $i++) {
            DB::table('customers')->insert(['dataset_id' => 1, 'customer_id' => sprintf('customer-%03d', $i), 'customer_unique_id' => 'unique-'.$i]);
        }
        $this->get(route('customers'))->assertOk()
            ->assertViewHas('customers', fn ($rows) => $rows->count() === 100)
            ->assertSee('customer-100')->assertDontSee('customer-101');
    }

    public function test_large_import_uses_batch_queries(): void
    {
        $rows = '';
        for ($i = 1; $i <= 1001; $i++) {
            $rows .= "customer-{$i},unique-{$i},12345,city,SP\n";
        }
        DB::enableQueryLog();
        $this->post(route('customers.import'), ['file' => $this->csv($rows)])->assertSessionHasNoErrors();
        $queries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'customers'));
        DB::disableQueryLog();
        $this->assertCount(6, $queries);
        $this->assertDatabaseCount('customers', 1001);
    }

    public function test_duplicate_after_first_batch_rolls_back_all_rows(): void
    {
        $rows = '';
        for ($i = 1; $i <= 500; $i++) {
            $rows .= "customer-{$i},unique-{$i},12345,city,SP\n";
        }
        $rows .= "customer-1,unique-other,12345,city,SP\n";
        $this->post(route('customers.import'), ['file' => $this->csv($rows)])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('customers', 0);
    }
}
