<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('datasets', 'rules_version')) {
            Schema::table('datasets', function (Blueprint $table) {
                $table->unsignedBigInteger('rules_version')->default(0);
            });
        }
        if (! Schema::hasTable('association_rule_runs')) {
            Schema::create('association_rule_runs', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('dataset_id');
                $table->string('cache_key', 64);
                $table->unsignedBigInteger('baskets');
                $table->unsignedBigInteger('items');
                $table->timestamp('created_at');
                $table->unique(['dataset_id', 'cache_key']);
                $table->foreign('dataset_id')->references('dataset_id')->on('datasets')->cascadeOnDelete();
            });
        }
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE association_rule_runs ENGINE = InnoDB');
        }
        Schema::table('association_rules', function (Blueprint $table) {
            if (Schema::hasColumn('association_rules', 'antecedent_product_id')) {
                $table->string('antecedent_product_id', 50)->nullable()->change();
                $table->string('consequent_product_id', 50)->nullable()->change();
            }
            $table->decimal('support', 20, 12)->nullable()->change();
            $table->decimal('confidence', 20, 12)->nullable()->change();
            $table->decimal('lift', 30, 12)->nullable()->change();
            if (! Schema::hasColumn('association_rules', 'run_id')) {
                $table->unsignedBigInteger('run_id')->nullable();
            }
            if (! Schema::hasColumn('association_rules', 'antecedent_category_name')) {
                $table->string('antecedent_category_name', 100)->nullable();
                $table->string('consequent_category_name', 100)->nullable();
            }
            if (! Schema::hasColumn('association_rules', 'pair_count')) {
                $table->unsignedBigInteger('pair_count')->nullable();
            }
            $table->foreign('run_id')->references('id')->on('association_rule_runs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('association_rules', function (Blueprint $table) {
            $table->dropForeign(['run_id']);
            $table->dropColumn(['run_id', 'pair_count']);
        });
        Schema::dropIfExists('association_rule_runs');
        Schema::table('datasets', fn (Blueprint $table) => $table->dropColumn('rules_version'));
    }
};
