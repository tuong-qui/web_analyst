<?php

namespace Tests\Feature;

use App\Services\AssociationRules;
use App\Services\ProductRecommendations;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\DatasetTestCase;

class ProductRecommendationTest extends DatasetTestCase
{
    public function test_recommendations_do_not_replace_saved_analysis_rules(): void
    {
        $this->post(route('basket.rules.analyze'), ['support' => 100])->assertRedirect();
        $this->assertDatabaseCount('association_rules', 0);

        $this->generate(['items' => ['Books']])
            ->assertOk()->assertViewHas('recommendations', fn ($rows) => $rows->isNotEmpty());

        $this->assertDatabaseCount('association_rules', 0);
        $this->get(route('basket.rules'))->assertOk()
            ->assertViewHas('filters', fn ($filters) => $filters['support'] == 100);
    }

    public function test_recommendations_are_saved_and_shown_without_recalculating(): void
    {
        $first = $this->generate(['items' => ['Books']])->viewData('recommendations');
        $ids = DB::table('bundle_recommendations')->pluck('bundle_id')->all();
        DB::table('products')->where('product_id', 'b')->update(['product_category_name' => 'Changed']);
        $this->partialMock(AssociationRules::class)->shouldNotReceive('calculate');

        $response = $this->get(route('basket.recommendations'))->assertOk();

        $this->assertSame($first->all(), $response->viewData('recommendations')->all());
        $this->assertSame(['Books'], $response->viewData('items'));
        $this->assertSame($ids, DB::table('bundle_recommendations')->pluck('bundle_id')->all());
        $this->assertFalse(Schema::hasTable('recommendation_runs'));
    }

    public function test_new_cart_or_threshold_replaces_the_previous_result(): void
    {
        $this->generate(['items' => ['Books']]);
        $this->assertDatabaseCount('bundle_recommendations', 1);
        $this->generate(['items' => ['Toys']]);
        $this->assertDatabaseHas('bundle_recommendations', ['main_category_name' => 'Toys', 'related_category_name' => 'Books']);
        $this->assertDatabaseMissing('bundle_recommendations', ['main_category_name' => 'Books']);
        $this->generate(['items' => ['Books'], 'lift' => 100]);
        $this->assertDatabaseCount('bundle_recommendations', 0);
        $this->get(route('basket.recommendations'))->assertOk()
            ->assertViewHas('items', ['Books'])
            ->assertViewHas('filters', fn ($filters) => $filters['lift'] == 100);
        $this->generate(['items' => ['Books']]);
        AssociationRules::invalidate(1);
        $this->assertDatabaseCount('bundle_recommendations', 0);
        $this->assertDatabaseHas('datasets', ['dataset_id' => 1, 'recommendation_context' => null]);
    }

    private function generate(array $parameters = []): TestResponse
    {
        $this->post(route('basket.recommendations.generate'), $parameters)->assertRedirect();

        return $this->get(route('basket.recommendations', ['dataset_id' => $parameters['dataset_id'] ?? 1]))->assertOk();
    }

    public function test_reset_clears_the_saved_cart_and_results(): void
    {
        $this->generate(['items' => ['Books']]);

        $this->generate()->assertViewHas('items', [])
            ->assertViewHas('recommendations', fn ($rows) => $rows->isEmpty());

        $this->assertDatabaseCount('bundle_recommendations', 0);
        $this->get(route('basket.recommendations'))->assertOk()->assertViewHas('items', []);
    }

    public function test_failed_replacement_keeps_previous_recommendations_and_cart(): void
    {
        $this->generate(['items' => ['Books']]);
        $previous = DB::table('bundle_recommendations')->get()->toJson();
        $context = DB::table('datasets')->where('dataset_id', 1)->value('recommendation_context');
        DB::statement("CREATE TRIGGER reject_recommendation BEFORE INSERT ON bundle_recommendations BEGIN SELECT RAISE(ABORT, 'Test insert failure'); END");

        $this->post(route('basket.recommendations.generate'), ['items' => ['Toys']])->assertServerError();

        $this->assertSame($previous, DB::table('bundle_recommendations')->get()->toJson());
        $this->assertDatabaseHas('datasets', ['dataset_id' => 1, 'recommendation_context' => $context]);
    }

    public function test_migration_preserves_latest_result_cart_and_empty_results(): void
    {
        Schema::table('datasets', fn (Blueprint $table) => $table->dropColumn('recommendation_context'));
        Schema::create('recommendation_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dataset_id');
            $table->json('selected_items');
        });
        Schema::table('bundle_recommendations', function (Blueprint $table) {
            $table->foreignId('recommendation_run_id')->nullable()->constrained('recommendation_runs')->cascadeOnDelete();
        });
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Second']);
        foreach ([[1, 1], [2, 1], [3, 2], [4, 2]] as [$id, $datasetId]) {
            DB::table('recommendation_runs')->insert([
                'id' => $id, 'dataset_id' => $datasetId, 'selected_items' => '["Books"]',
            ]);
        }
        foreach ([[1, 1], [1, 2], [2, 3]] as [$datasetId, $runId]) {
            DB::table('bundle_recommendations')->insert([
                'dataset_id' => $datasetId, 'user_id' => 1, 'recommendation_run_id' => $runId,
                'main_category_name' => 'Books', 'related_category_name' => 'Toys',
            ]);
        }

        (require database_path('migrations/2026_10_29_000001_replace_recommendation_runs_with_latest_recommendations.php'))->up();

        $this->assertDatabaseCount('bundle_recommendations', 1);
        $this->assertDatabaseHas('bundle_recommendations', ['dataset_id' => 1]);
        $this->assertDatabaseMissing('bundle_recommendations', ['dataset_id' => 2]);
        $this->assertFalse(Schema::hasTable('recommendation_runs'));
        $this->assertFalse(Schema::hasColumn('bundle_recommendations', 'recommendation_run_id'));
        $this->get(route('basket.recommendations', ['dataset_id' => 2]))->assertOk()
            ->assertViewHas('items', ['Books'])
            ->assertViewHas('recommendations', fn ($rows) => $rows->isEmpty());
    }

    public function test_recommendations_are_separate_between_datasets(): void
    {
        $this->generate(['items' => ['Books']])->assertOk();
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Shop B']);
        DB::table('products')->insert(['dataset_id' => 2, 'product_id' => 'a', 'product_category_name' => 'Books']);
        DB::table('customers')->insert(['dataset_id' => 2, 'customer_id' => 'c']);
        DB::table('orders')->insert(['dataset_id' => 2, 'customer_id' => 'c', 'order_id' => 'one']);
        DB::table('order_items')->insert(['dataset_id' => 2, 'product_id' => 'a', 'order_id' => 'one']);
        $this->generate(['dataset_id' => 2, 'items' => ['Books']])
            ->assertOk()->assertViewHas('recommendations', fn ($rows) => $rows->isEmpty());
        AssociationRules::invalidate(2);
        $this->assertGreaterThan(0, DB::table('bundle_recommendations')->where('dataset_id', 1)->count());
    }

    public function test_choices_are_available_without_calculating_rules_and_remain_after_empty_results(): void
    {
        $this->get(route('basket.recommendations'))->assertOk()
            ->assertViewHas('options', fn ($options) => $options->contains('Books') && $options->contains('Toys'));
        $response = $this->generate(['items' => ['Books'], 'lift' => 100]);
        $this->assertTrue($response->viewData('options')->contains('Toys'));
        $response->assertSee('checked', false);
    }

    public function test_only_top_twenty_choices_are_initially_visible_but_search_can_reach_the_rest(): void
    {
        foreach (range(1, 25) as $number) {
            DB::table('products')->insert(['dataset_id' => 1, 'product_id' => "extra-$number", 'product_category_name' => "Name-$number"]);
            DB::table('order_items')->insert(['dataset_id' => 1, 'order_id' => 'one', 'product_id' => "extra-$number"]);
        }
        $this->partialMock(AssociationRules::class)->shouldNotReceive('calculate');
        $response = $this->get(route('basket.recommendations'))->assertOk();
        $this->assertCount(28, $response->viewData('options'));
        preg_match_all('/<label class="recommend-option"([^>]*)>/', $response->getContent(), $labels);
        $this->assertCount(8, array_filter($labels[1], fn ($attributes) => str_contains($attributes, 'hidden')));
        $response->assertSee('value="Name-25"', false);
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
        DB::table('customers')->insert(['dataset_id' => 1, 'customer_id' => 'c']);
        foreach (['a' => 'Books', 'b' => 'Toys', 'c' => '<script>unsafe()</script>'] as $id => $name) {
            DB::table('products')->insert(['dataset_id' => 1, 'product_id' => $id, 'product_category_name' => $name]);
        }
        foreach (['one' => ['a', 'b', 'c'], 'two' => ['a', 'b'], 'three' => ['c']] as $order => $items) {
            DB::table('orders')->insert(['dataset_id' => 1, 'order_id' => $order, 'customer_id' => 'c', 'order_status' => 'delivered']);
            foreach ($items as $item) {
                DB::table('order_items')->insert(['dataset_id' => 1, 'order_id' => $order, 'product_id' => $item]);
            }
        }
    }

    public function test_selected_cart_produces_ranked_explained_and_escaped_recommendations(): void
    {
        $response = $this->generate(['items' => ['Books']])->assertOk();
        $recommendations = $response->viewData('recommendations');
        $this->assertSame('Toys', $recommendations->first()['consequent']);
        $this->assertSame('Books', $recommendations->first()['antecedent']);
        $this->assertEquals(100, $recommendations->first()['confidence']);
        $this->assertFalse($recommendations->contains('consequent', 'Books'));
        $response->assertDontSee('<script>unsafe()</script>', false);
        $both = $this->generate(['items' => ['Books', 'Toys'], 'lift' => 0])->assertOk();
        $this->assertCount(1, $both->viewData('recommendations'));
    }

    public function test_empty_cart_empty_results_and_thresholds(): void
    {
        $this->get(route('basket.recommendations'))->assertOk()->assertViewHas('recommendations', fn ($items) => $items->isEmpty());
        $this->generate(['items' => ['Books'], 'lift' => 100])
            ->assertOk()->assertViewHas('recommendations', fn ($items) => $items->isEmpty());
        DB::table('order_items')->delete();
        AssociationRules::invalidate(1);
        $this->get(route('basket.recommendations'))->assertOk()->assertViewHas('options', fn ($items) => $items->isEmpty());
    }

    public function test_dataset_ownership_and_invalid_input(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 99, 'name' => 'Private']);
        $this->get(route('basket.recommendations', ['dataset_id' => 2]))->assertNotFound();
        $this->post(route('basket.recommendations.generate'), ['dataset_id' => 2, 'items' => ['Books']])->assertNotFound();
        $this->postJson(route('basket.recommendations.generate'), ['items' => ['unknown']])->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->postJson(route('basket.recommendations.generate'), ['items' => 'Books', 'support' => 0])->assertUnprocessable()->assertJsonValidationErrors(['items', 'support']);
        $this->postJson(route('basket.recommendations.generate'), ['items' => ['Books', 'Books']])->assertUnprocessable()->assertJsonValidationErrors('items.0');
        DB::table('datasets')->where('dataset_id', 1)->update(['user_id' => 99]);
        $this->get(route('basket.recommendations'))->assertRedirect(route('data'));
        auth()->forgetGuards();
        $this->get(route('basket.recommendations'))->assertRedirect(route('login'));
        $this->post(route('basket.recommendations.generate'), ['items' => ['Books']])->assertRedirect(route('login'));
    }

    public function test_candidates_are_deduplicated_excluded_and_limited(): void
    {
        $rules = collect();
        foreach (range(1, 12) as $number) {
            $rules->push(['antecedent' => 'A', 'consequent' => "item-$number"]);
            $rules->push(['antecedent' => 'B', 'consequent' => "item-$number"]);
        }
        $result = app(ProductRecommendations::class)->fromRules($rules, ['A', 'B', 'item-1']);
        $this->assertCount(10, $result);
        $this->assertSame('item-2', $result->first()['consequent']);
        $this->assertSame('A', $result->first()['antecedent']);
        $this->assertCount(10, $result->pluck('consequent')->unique());
    }
}
