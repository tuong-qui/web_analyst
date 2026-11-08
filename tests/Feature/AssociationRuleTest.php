<?php

namespace Tests\Feature;

use App\Services\AssociationRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\DatasetTestCase;

class AssociationRuleTest extends DatasetTestCase
{
    public function test_saved_rules_are_shown_without_recalculating(): void
    {
        $this->seedBaskets();
        $this->get(route('basket.rules'))->assertOk()->assertViewHas('rules', fn ($rules) => $rules->isEmpty());
        $first = $this->analyze(route('basket.rules'))->viewData('analysis');
        $ids = DB::table('association_rules')->pluck('rule_id')->all();
        DB::table('products')->where('product_id', 'b')->update(['product_category_name' => 'Changed']);
        $second = $this->get(route('basket.rules'))->assertOk()->viewData('analysis');
        $this->assertSame($ids, DB::table('association_rules')->pluck('rule_id')->all());
        $this->assertSame($first['rules']->pluck('consequent')->all(), $second['rules']->pluck('consequent')->all());
        $this->assertFalse(Schema::hasTable('association_rule_runs'));
    }

    public function test_new_analysis_replaces_previous_rules_including_empty_results(): void
    {
        $this->seedBaskets();
        $this->analyze(route('basket.rules'));
        $this->assertDatabaseCount('association_rules', 2);
        $this->analyze(route('basket.rules', ['support' => 100]));
        $this->assertDatabaseCount('association_rules', 0);
        $this->get(route('basket.rules'))->assertOk()->assertViewHas('filters', fn ($filters) => $filters['support'] == 100);
        $this->assertDatabaseCount('association_rules', 0);
        $this->analyze(route('basket.rules'));
        $this->assertDatabaseCount('association_rules', 2);
    }

    private function analyze(string $url): TestResponse
    {
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $parameters);
        $this->post(route('basket.rules.analyze'), $parameters)->assertRedirect();

        return $this->get(route('basket.rules', $parameters))->assertOk();
    }

    public function test_invalidating_a_dataset_removes_old_rules_and_recalculates(): void
    {
        $this->seedBaskets();
        $this->analyze(route('basket.rules'))->assertOk();
        AssociationRules::invalidate(1);
        $this->assertSame(0, DB::table('association_rules')->count());
        DB::table('products')->where('product_id', 'b')->update(['product_category_name' => 'Books']);
        $response = $this->analyze(route('basket.rules'))->assertOk();
        $this->assertCount(0, $response->viewData('analysis')['rules']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('products', function (Blueprint $table) {
            $table->integer('dataset_id');
            $table->string('product_id');
            $table->string('product_category_name')->nullable();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->integer('dataset_id');
            $table->string('order_id');
            $table->string('product_id');
        });
    }

    private function seedBaskets(): void
    {
        DB::table('customers')->insert(['dataset_id' => 1, 'customer_id' => 'c']);
        foreach (['a' => 'Books', 'b' => 'Toys', 'c' => 'Clothes'] as $id => $category) {
            DB::table('products')->insert(['dataset_id' => 1, 'product_id' => $id, 'product_category_name' => $category]);
        }
        foreach (['one' => ['a', 'a', 'b'], 'two' => ['a', 'b'], 'three' => ['a'], 'four' => ['b'], 'five' => ['c'], 'canceled' => ['a', 'b'], 'unavailable' => ['a', 'b']] as $order => $items) {
            DB::table('orders')->insert(['dataset_id' => 1, 'order_id' => $order, 'customer_id' => 'c', 'order_status' => in_array($order, ['canceled', 'unavailable']) ? $order : 'delivered']);
            foreach ($items as $item) {
                DB::table('order_items')->insert(['dataset_id' => 1, 'order_id' => $order, 'product_id' => $item]);
            }
        }
    }

    public function test_rules_use_unique_items_and_include_single_item_baskets_but_exclude_canceled_orders(): void
    {
        $this->seedBaskets();
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 99, 'name' => 'Private']);
        DB::table('products')->insert(['dataset_id' => 2, 'product_id' => 'a', 'product_category_name' => 'Private category']);

        $response = $this->analyze(route('basket.rules'))->assertOk()->assertDontSee('Private category');
        $data = $response->viewData('analysis');
        $this->assertSame(5, $data['baskets']);
        $this->assertSame(3, $data['items']);
        $this->assertCount(2, $data['rules']);
        $rule = $data['rules']->firstWhere('antecedent', 'Books');
        $this->assertSame('Toys', $rule['consequent']);
        $this->assertSame(2, $rule['count']);
        $this->assertEqualsWithDelta(40, $rule['support'], 0.0001);
        $this->assertEqualsWithDelta(66.6666667, $rule['confidence'], 0.0001);
        $this->assertEqualsWithDelta(1.1111111, $rule['lift'], 0.0001);
    }

    public function test_each_threshold_filters_rules_and_legacy_level_does_not_change_names(): void
    {
        $this->seedBaskets();

        foreach (['support' => 41, 'confidence' => 67, 'lift' => 1.2] as $key => $value) {
            $response = $this->analyze(route('basket.rules', [$key => $value]))->assertOk();
            $this->assertCount(0, $response->viewData('analysis')['rules']);
        }
        $response = $this->analyze(route('basket.rules', ['level' => 'product', 'support' => 40]))->assertOk();
        $this->assertCount(2, $response->viewData('analysis')['rules']);
        $this->assertSame('Books', $response->viewData('analysis')['rules']->first()['antecedent']);
    }

    public function test_confidence_is_directional(): void
    {
        $this->seedBaskets();
        DB::table('order_items')->where('order_id', 'three')->update(['product_id' => 'c']);

        $response = $this->analyze(route('basket.rules', ['confidence' => 80]))->assertOk();

        $rules = $response->viewData('analysis')['rules'];
        $this->assertCount(1, $rules);
        $this->assertSame('Books', $rules->first()['antecedent']);
        $this->assertSame('Toys', $rules->first()['consequent']);
        $this->assertEquals(100, $rules->first()['confidence']);
    }

    public function test_same_category_products_do_not_create_a_self_rule_and_unknown_categories_are_excluded(): void
    {
        $this->seedBaskets();
        DB::table('products')->where('product_id', 'b')->update(['product_category_name' => 'Books']);
        DB::table('products')->where('product_id', 'c')->update(['product_category_name' => null]);

        $response = $this->analyze(route('basket.rules'))->assertOk();
        $this->assertSame(4, $response->viewData('analysis')['baskets']);
        $this->assertCount(0, $response->viewData('analysis')['rules']);
    }

    public function test_empty_data_and_user_without_datasets_have_useful_responses(): void
    {
        $response = $this->analyze(route('basket.rules'))->assertOk()->assertSee('Chưa có giỏ hàng hợp lệ');
        $this->assertSame(0, $response->viewData('analysis')['baskets']);
        DB::table('datasets')->delete();
        $this->get(route('basket.rules'))->assertRedirect(route('data'));
    }

    public function test_other_users_dataset_is_not_accessible(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 99, 'name' => 'Private']);

        $this->get(route('basket.rules', ['dataset_id' => 2]))->assertNotFound();
        $this->post(route('basket.rules.analyze'), ['dataset_id' => 2])->assertNotFound();
    }

    public function test_guest_must_log_in(): void
    {
        auth()->forgetGuards();

        $this->get(route('basket.rules'))->assertRedirect(route('login'));
        $this->post(route('basket.rules.analyze'))->assertRedirect(route('login'));
    }

    public function test_invalid_thresholds_and_dynamic_column_input_are_rejected(): void
    {
        $this->getJson(route('basket.rules', ['support' => 0, 'confidence' => 101, 'lift' => -1, 'level' => 'i.product_id; DROP TABLE orders']))
            ->assertUnprocessable()->assertJsonValidationErrors(['support', 'confidence', 'lift']);
        $this->postJson(route('basket.rules.analyze'), ['support' => 0, 'confidence' => 101, 'lift' => -1])
            ->assertUnprocessable()->assertJsonValidationErrors(['support', 'confidence', 'lift']);
        $this->assertDatabaseCount('association_rules', 0);
    }

    public function test_migration_keeps_latest_rules_and_respects_latest_empty_result(): void
    {
        Schema::table('datasets', fn (Blueprint $table) => $table->dropColumn('rules_filters'));
        Schema::create('association_rule_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dataset_id');
        });
        Schema::table('association_rules', function (Blueprint $table) {
            $table->foreignId('run_id')->nullable()->constrained('association_rule_runs')->cascadeOnDelete();
        });
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Second']);
        DB::table('association_rule_runs')->insert([
            ['id' => 1, 'dataset_id' => 1], ['id' => 2, 'dataset_id' => 1],
            ['id' => 3, 'dataset_id' => 2], ['id' => 4, 'dataset_id' => 2],
        ]);
        foreach ([[1, 1], [1, 2], [2, 3]] as [$datasetId, $runId]) {
            DB::table('association_rules')->insert([
                'dataset_id' => $datasetId, 'user_id' => 1, 'run_id' => $runId,
                'antecedent_category_name' => 'Books', 'consequent_category_name' => 'Toys',
            ]);
        }

        (require database_path('migrations/2026_10_29_000000_replace_association_rule_runs_with_latest_rules.php'))->up();

        $this->assertDatabaseCount('association_rules', 1);
        $this->assertDatabaseHas('association_rules', ['dataset_id' => 1]);
        $this->assertDatabaseMissing('association_rules', ['dataset_id' => 2]);
        $this->assertFalse(Schema::hasColumn('association_rules', 'run_id'));
        $this->assertFalse(Schema::hasTable('association_rule_runs'));
    }

    public function test_category_names_and_dataset_names_are_escaped(): void
    {
        $this->seedBaskets();
        DB::table('datasets')->where('dataset_id', 1)->update(['name' => '<script>dataset()</script>']);
        DB::table('products')->where('product_id', 'a')->update(['product_category_name' => '<script>item()</script>']);

        $this->analyze(route('basket.rules'))->assertOk()
            ->assertSee('<script>dataset()</script>')->assertSee('<script>item()</script>')
            ->assertDontSee('<script>dataset()</script>', false)->assertDontSee('<script>item()</script>', false);
    }
}
