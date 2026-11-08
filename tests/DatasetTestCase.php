<?php

namespace Tests;

use Database\Factories\UserFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class DatasetTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('datasets', function (Blueprint $table) {
            $table->bigIncrements('dataset_id');
            $table->integer('user_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('dataset_id');
            $table->string('customer_id');
            $table->string('customer_unique_id')->nullable();
            $table->integer('customer_zip_code_prefix')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_state')->nullable();
            $table->primary(['dataset_id', 'customer_id']);
            $table->foreign('dataset_id')->references('dataset_id')->on('datasets');
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('dataset_id');
            $table->string('order_id');
            $table->string('customer_id');
            $table->string('order_status')->nullable();
            foreach (['order_purchase_timestamp', 'order_approved_at', 'order_delivered_carrier_date', 'order_delivered_customer_date', 'order_estimated_delivery_date'] as $column) {
                $table->dateTime($column)->nullable();
            }
            $table->primary(['dataset_id', 'order_id']);
            $table->foreign(['dataset_id', 'customer_id'])->references(['dataset_id', 'customer_id'])->on('customers');
        });
        DB::table('datasets')->insert(['dataset_id' => 1, 'user_id' => 1, 'name' => 'Shop A']);
        Schema::create('association_rules', function (Blueprint $table) {
            $table->increments('rule_id');
            $table->integer('user_id');
            $table->unsignedBigInteger('dataset_id');
            $table->string('antecedent_product_id', 50);
            $table->string('consequent_product_id', 50);
            $table->decimal('support', 8, 6)->nullable();
            $table->decimal('confidence', 8, 6)->nullable();
            $table->decimal('lift', 8, 6)->nullable();
            $table->timestamp('created_at')->nullable();
        });
        (require database_path('migrations/2026_10_26_000000_persist_association_rules.php'))->up();
        (require database_path('migrations/2026_10_29_000000_replace_association_rule_runs_with_latest_rules.php'))->up();
        Schema::create('bundle_recommendations', function (Blueprint $table) {
            $table->increments('bundle_id');
            $table->integer('user_id');
            $table->unsignedBigInteger('dataset_id');
            $table->string('main_product_id', 50);
            $table->string('related_product_id', 50);
            $table->decimal('confidence', 8, 6)->nullable();
            $table->decimal('lift', 8, 6)->nullable();
            $table->string('status')->default('active');
            $table->timestamp('created_at')->nullable();
        });
        (require database_path('migrations/2026_10_26_000001_persist_product_recommendations.php'))->up();
        (require database_path('migrations/2026_10_29_000001_replace_recommendation_runs_with_latest_recommendations.php'))->up();
        $this->actingAs(UserFactory::new()->make(['user_id' => 1]));
    }
}
