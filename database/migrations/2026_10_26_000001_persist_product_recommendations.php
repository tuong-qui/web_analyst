<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_runs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('dataset_id');
            $table->string('cache_key', 64);
            $table->json('selected_items');
            $table->timestamp('created_at');
            $table->unique(['dataset_id', 'cache_key']);
            $table->foreign('dataset_id')->references('dataset_id')->on('datasets')->cascadeOnDelete();
        });
        Schema::table('bundle_recommendations', function (Blueprint $table) {
            $table->string('main_product_id', 50)->nullable()->change();
            $table->string('related_product_id', 50)->nullable()->change();
            $table->decimal('confidence', 20, 12)->nullable()->change();
            $table->decimal('lift', 30, 12)->nullable()->change();
            $table->unsignedBigInteger('recommendation_run_id')->nullable();
            $table->string('main_category_name', 100)->nullable();
            $table->string('related_category_name', 100)->nullable();
            $table->decimal('support', 20, 12)->nullable();
            $table->unsignedBigInteger('pair_count')->nullable();
            $table->unsignedTinyInteger('rank')->nullable();
            $table->foreign('recommendation_run_id')->references('id')->on('recommendation_runs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bundle_recommendations', function (Blueprint $table) {
            $table->dropForeign(['recommendation_run_id']);
            $table->dropColumn(['recommendation_run_id', 'main_category_name', 'related_category_name', 'support', 'pair_count', 'rank']);
        });
        Schema::dropIfExists('recommendation_runs');
    }
};
