<?php

namespace Tests\Feature;

use App\Services\CustomerSegments;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DatasetTestCase;

class CustomerSegmentTest extends DatasetTestCase
{
    public function test_opening_the_page_saves_all_customers_before_filtering_and_pagination(): void
    {
        foreach (range(1, 30) as $id) {
            $this->order('order-'.$id, 'person-'.$id, '2026-01-01', [$id]);
        }

        $this->get(route('customers.segments', ['search' => 'absent', 'page' => 2]))->assertOk();

        $this->assertSame(30, DB::table('customer_segments')->count());
        $saved = DB::table('customer_segments')->where('customer_unique_id', 'person-1')->first();
        $this->assertSame('2026-01-01', $saved->analysis_date);
        $this->assertSame(1, (int) $saved->frequency);
        $this->assertSame(0, (int) $saved->recency);
        $this->assertEquals(1, $saved->monetary);
        $this->assertSame('new', $saved->segment);
        $this->assertSame(5, (int) $saved->r_score);
        $this->assertNotNull($saved->analyzed_at);
    }

    public function test_snapshots_replace_same_date_remove_stale_people_and_preserve_other_dates(): void
    {
        $this->order('first', 'person', '2026-01-01', [100, 20]);
        $this->order('second', 'person', '2026-01-31', [30]);
        $this->order('stale', 'stale', '2026-01-01', [10]);
        $this->get(route('customers.segments', ['date' => '2026-01-01']))->assertOk();
        $this->get(route('customers.segments', ['date' => '2026-01-31']))->assertOk();

        DB::table('orders')->where('order_id', 'stale')->update(['order_status' => 'canceled']);
        DB::table('order_payments')->where('order_id', 'second')->update(['payment_value' => 50]);
        $this->get(route('customers.segments', ['date' => '2026-01-31']))->assertOk();
        $this->get(route('customers.segments', ['date' => '2026-01-31']))->assertOk();

        $this->assertSame(3, DB::table('customer_segments')->count());
        $this->assertSame(1, DB::table('customer_segments')->where('analysis_date', '2026-01-31')->count());
        $this->assertEquals(170, DB::table('customer_segments')->where('analysis_date', '2026-01-31')->value('monetary'));
        $this->assertEquals(120, DB::table('customer_segments')->where('analysis_date', '2026-01-01')
            ->where('customer_unique_id', 'person')->value('monetary'));

        DB::table('orders')->update(['order_status' => 'canceled']);
        $this->get(route('customers.segments', ['date' => '2026-01-31']))->assertOk();
        $this->assertSame(0, DB::table('customer_segments')->where('analysis_date', '2026-01-31')->count());
        $this->assertSame(2, DB::table('customer_segments')->where('analysis_date', '2026-01-01')->count());
    }

    public function test_unauthorized_datasets_cannot_create_or_replace_snapshots(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 99, 'name' => 'Private']);
        $this->order('other', 'same-person', '2026-01-01', [900], dataset: 2);
        $this->get(route('customers.segments', ['dataset_id' => 2]))->assertNotFound();
        $this->assertSame(0, DB::table('customer_segments')->count());

        $this->order('own', 'same-person', '2026-01-01', [10]);
        $this->get(route('customers.segments'))->assertOk();
        $this->assertSame(1, DB::table('customer_segments')->count());
        $this->assertEquals(10, DB::table('customer_segments')->value('monetary'));
        $this->assertSame(1, (int) DB::table('customer_segments')->value('dataset_id'));
    }

    public function test_failed_replacement_keeps_the_previous_snapshot(): void
    {
        $this->order('own', 'person', '2026-01-01', [10]);
        $this->get(route('customers.segments'))->assertOk();
        DB::table('order_payments')->update(['payment_value' => 20]);
        DB::unprepared("CREATE TRIGGER reject_snapshot BEFORE INSERT ON customer_segments BEGIN SELECT RAISE(ABORT, 'snapshot failure'); END");

        $this->withoutExceptionHandling();
        try {
            $this->get(route('customers.segments'));
            $this->fail('The failed insert must not succeed.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString('snapshot failure', $exception->getMessage());
        }

        $this->assertSame(1, DB::table('customer_segments')->count());
        $this->assertEquals(10, DB::table('customer_segments')->value('monetary'));
    }

    public function test_customer_records_are_paginated_in_sql(): void
    {
        foreach (range(1, 30) as $id) {
            $this->order('customer-'.$id, 'person-'.$id, '2026-01-01', [$id]);
        }
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $response = $this->get(route('customers.segments', ['page' => 2]))->assertOk();
        $customers = $response->viewData('customers');
        $this->assertSame(30, $customers->total());
        $this->assertCount(5, $customers);
        $this->assertSame('person-5', $customers->first()['customer']);
        $this->assertTrue(collect($queries)->contains(fn ($sql) => str_contains(strtolower($sql), 'limit 25 offset 25')));
    }

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_10_26_000002_create_customer_segments_table.php'))->up();
        Schema::create('order_payments', function (Blueprint $table) {
            $table->integer('dataset_id');
            $table->string('order_id');
            $table->decimal('payment_value', 12, 2);
        });
    }

    private function order(string $id, ?string $person, ?string $date, array $payments = [], string $status = 'delivered', int $dataset = 1): void
    {
        DB::table('customers')->insert(['dataset_id' => $dataset, 'customer_id' => $id, 'customer_unique_id' => $person]);
        DB::table('orders')->insert(['dataset_id' => $dataset, 'order_id' => $id, 'customer_id' => $id, 'order_purchase_timestamp' => $date, 'order_status' => $status]);
        foreach ($payments as $payment) {
            DB::table('order_payments')->insert(['dataset_id' => $dataset, 'order_id' => $id, 'payment_value' => $payment]);
        }
    }

    public function test_rfm_merges_identities_without_multiplying_orders_and_ignores_invalid_data(): void
    {
        $this->order('one', 'person', '2026-01-01 10:00:00', [100, 20]);
        $this->order('two', 'person', '2026-01-31 23:59:59', [30]);
        $this->order('unpaid', 'unpaid', '2026-01-30');
        $this->order('cancel', 'person', '2027-01-01', [999], 'canceled');
        $this->order('missing', null, '2028-01-01', [999]);
        $this->order('undated', 'person', null, [999]);
        $this->order('unavailable', 'person', '2028-01-01', [999], 'unavailable');
        $response = $this->get(route('customers.segments'))->assertOk();
        $data = $response->viewData('analysis');
        $this->assertSame('2026-01-31', $data['date']);
        $this->assertSame(2, $data['total']);
        $this->assertSame(3, $data['orders']);
        $this->assertEquals(150, $data['monetary']);
        $person = $data['customers']->get()->map(fn ($row) => (array) $row)->firstWhere('customer', 'person');
        $this->assertSame(0, $person['recency']);
        $this->assertSame(2, $person['frequency']);
        $this->assertEquals(150, $person['monetary']);
        $this->assertEquals(0, $data['customers']->get()->map(fn ($row) => (array) $row)->firstWhere('customer', 'unpaid')['monetary']);
        $this->assertSame(2, $data['groups']->sum('count'));
        $earlier = app(CustomerSegments::class)->analyze(1, '2026-01-01');
        $this->assertSame(1, $earlier['orders']);
        $this->assertEquals(120, $earlier['monetary']);
    }

    public function test_all_segments_have_explicit_precedence_and_filters_work(): void
    {
        foreach (['vip' => ['2026-12-31', 3, 1000], 'loyal' => ['2026-12-31', 3, 0], 'new' => ['2026-12-31', 1, 1], 'risk' => ['2026-08-01', 2, 1], 'inactive' => ['2026-01-01', 1, 1], 'potential' => ['2026-12-31', 2, 1]] as $person => [$date, $count, $value]) {
            foreach (range(1, $count) as $number) {
                $this->order("$person-$number", $person, $date, [$value]);
            }
        }
        $data = app(CustomerSegments::class)->analyze(1);
        foreach (['vip' => 'champions', 'loyal' => 'loyal', 'new' => 'new', 'risk' => 'at_risk', 'inactive' => 'inactive', 'potential' => 'potential'] as $person => $segment) {
            $this->assertSame($segment, $data['customers']->get()->map(fn ($row) => (array) $row)->firstWhere('customer', $person)['segment']);
        }
        $this->get(route('customers.segments', ['segment' => 'champions', 'search' => 'vip']))->assertOk()
            ->assertViewHas('customers', fn ($rows) => $rows->total() === 1 && $rows->first()['customer'] === 'vip');
        $this->get(route('customers.segments', ['sort' => 'recency', 'search' => 'absent']))->assertOk()->assertViewHas('customers', fn ($rows) => $rows->total() === 0);
    }

    public function test_dataset_isolation_empty_data_and_validation(): void
    {
        $this->get(route('customers.segments'))->assertOk()->assertViewHas('analysis', fn ($data) => $data['total'] === 0);
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 99, 'name' => 'Private']);
        $this->order('same', 'private', '2026-01-01', [900], dataset: 2);
        $this->get(route('customers.segments', ['dataset_id' => 2]))->assertNotFound();
        $this->order('same', '<script>unsafe()</script>', '2026-01-01', [10]);
        $this->get(route('customers.segments'))->assertOk()->assertDontSee('<script>unsafe()</script>', false)
            ->assertViewHas('analysis', fn ($data) => $data['monetary'] === 10.0);
        $this->getJson(route('customers.segments', ['date' => '2026-02-30', 'segment' => 'bad', 'sort' => 'bad']))->assertUnprocessable()->assertJsonValidationErrors(['date', 'segment', 'sort']);
        $this->get(route('customers.segments', ['date' => '2025-01-01']))->assertOk()->assertViewHas('analysis', fn ($data) => $data['total'] === 0);
        DB::table('datasets')->where('dataset_id', 1)->update(['user_id' => 99]);
        $this->get(route('customers.segments'))->assertRedirect(route('data'));
        auth()->forgetGuards();
        $this->get(route('customers.segments'))->assertRedirect(route('login'));
    }
}
