<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\DatasetTestCase;

class OrderImportTest extends DatasetTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('customers')->insert(['dataset_id' => 1, 'customer_id' => 'customer-1']);
    }

    private function csv(string $rows): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('orders.csv',
            "\xEF\xBB\xBForder_id,customer_id,order_status,order_purchase_timestamp,order_approved_at,order_delivered_carrier_date,order_delivered_customer_date,order_estimated_delivery_date\n".$rows);
    }

    public function test_import_saves_dates_and_nullable_fields(): void
    {
        $this->post(route('orders.import'), ['file' => $this->csv("order-1,customer-1,delivered,2026-10-14 10:00:00,,,,\n")])
            ->assertRedirect(route('orders'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 'order-1',
            'order_purchase_timestamp' => '2026-10-14 10:00:00',
            'order_approved_at' => null,
        ]);
    }

    public function test_unknown_customer_rolls_back_previous_rows(): void
    {
        $this->post(route('orders.import'), ['file' => $this->csv("order-1,customer-1,created,,,,,\norder-2,missing,created,,,,,\n")])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_duplicate_order_rolls_back_import(): void
    {
        $this->post(route('orders.import'), ['file' => $this->csv("order-1,customer-1,created,,,,,\norder-1,customer-1,created,,,,,\n")])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_invalid_date_is_rejected(): void
    {
        $this->post(route('orders.import'), ['file' => $this->csv("order-1,customer-1,created,invalid,,,,\n")])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_large_order_import_uses_batch_queries(): void
    {
        $rows = '';
        for ($i = 1; $i <= 1001; $i++) {
            $rows .= "order-{$i},customer-1,created,2026-10-14 10:00:00,,,,\n";
        }
        DB::enableQueryLog();
        $this->post(route('orders.import'), ['file' => $this->csv($rows)])->assertSessionHasNoErrors();
        $queries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'orders') || str_contains($query['query'], 'customers'));
        DB::disableQueryLog();
        $this->assertCount(9, $queries);
        $this->assertDatabaseCount('orders', 1001);
    }

    public function test_missing_customer_after_first_batch_rolls_back_everything(): void
    {
        $rows = '';
        for ($i = 1; $i <= 500; $i++) {
            $rows .= "order-{$i},customer-1,created,,,,,\n";
        }
        $rows .= "order-missing,missing,created,,,,,\n";
        $this->post(route('orders.import'), ['file' => $this->csv($rows)])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_duplicate_order_across_batches_rolls_back_everything(): void
    {
        $rows = '';
        for ($i = 1; $i <= 500; $i++) {
            $rows .= "order-{$i},customer-1,created,,,,,\n";
        }
        $rows .= "order-1,customer-1,created,,,,,\n";
        $this->post(route('orders.import'), ['file' => $this->csv($rows)])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_orders_page_limits_database_preview_to_100_rows(): void
    {
        for ($i = 1; $i <= 101; $i++) {
            DB::table('orders')->insert(['dataset_id' => 1, 'order_id' => sprintf('order-%03d', $i), 'customer_id' => 'customer-1']);
        }

        $this->get(route('orders'))->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->count() === 100)
            ->assertSee('order-001')->assertSee('order-100')->assertDontSee('order-101');
    }

    public function test_successful_import_redirect_displays_saved_data(): void
    {
        $this->followingRedirects()->post(route('orders.import'), [
            'file' => $this->csv("imported-order,customer-1,delivered,2026-10-14 10:00:00,,,,\n"),
        ])->assertOk()->assertSee('imported-order')->assertSee('2026-10-14 10:00:00');
    }

    public function test_imports_excel_and_ods_with_native_dates(): void
    {
        foreach (['Xlsx' => 'xlsx', 'Xls' => 'xls', 'Ods' => 'ods'] as $type => $extension) {
            $book = new Spreadsheet;
            $sheet = $book->getActiveSheet();
            $sheet->fromArray([
                ['order_id', 'customer_id', 'order_status', 'order_purchase_timestamp', 'order_approved_at', 'order_delivered_carrier_date', 'order_delivered_customer_date', 'order_estimated_delivery_date'],
                ['order-'.$extension, 'customer-1', 'created', Date::PHPToExcel(new \DateTime('2026-10-14 10:00:00'))],
            ]);
            $sheet->getStyle('D2')->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
            $path = tempnam(sys_get_temp_dir(), 'order-test-');
            try {
                IOFactory::createWriter($book, $type)->save($path);
                $file = new UploadedFile($path, 'orders.'.$extension, null, null, true);
                $this->post(route('orders.import'), ['file' => $file])->assertSessionHasNoErrors();
                $this->assertDatabaseHas('orders', ['order_id' => 'order-'.$extension, 'order_purchase_timestamp' => '2026-10-14 10:00:00']);
            } finally {
                $book->disconnectWorksheets();
                unlink($path);
            }
        }
    }

    public function test_imports_tsv(): void
    {
        $csv = $this->csv("order-tsv,customer-1,created,,,,,\n");
        $file = UploadedFile::fake()->createWithContent('orders.tsv', str_replace(',', "\t", file_get_contents($csv->getRealPath())));
        $this->post(route('orders.import'), ['file' => $file])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['order_id' => 'order-tsv']);
    }

    public function test_rejects_corrupt_excel_and_unsupported_files(): void
    {
        foreach (['xlsx', 'pdf'] as $extension) {
            $file = UploadedFile::fake()->createWithContent('orders.'.$extension, 'invalid file');
            $this->post(route('orders.import'), ['file' => $file])->assertSessionHasErrors('file');
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_rejects_spreadsheet_formulas(): void
    {
        $book = new Spreadsheet;
        $book->getActiveSheet()->setCellValue('A1', '=1+1');
        $path = tempnam(sys_get_temp_dir(), 'order-formula-');
        try {
            IOFactory::createWriter($book, 'Xlsx')->save($path);
            $this->post(route('orders.import'), ['file' => new UploadedFile($path, 'orders.xlsx', null, null, true)])
                ->assertSessionHasErrors('file');
            $this->assertDatabaseCount('orders', 0);
        } finally {
            $book->disconnectWorksheets();
            unlink($path);
        }
    }
}
