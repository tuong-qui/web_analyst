<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->json('rules_filters')->nullable();
        });

        // Preserve only the latest result, including a latest run with no rules.
        DB::table('datasets')->eachById(function ($dataset) {
            $latest = DB::table('association_rule_runs')->where('dataset_id', $dataset->dataset_id)->max('id');
            if ($latest !== null) {
                DB::table('association_rules')->where('dataset_id', $dataset->dataset_id)
                    ->where(fn ($query) => $query->whereNull('run_id')->orWhere('run_id', '<>', $latest))->delete();
            }
        }, 100, 'dataset_id');

        Schema::table('association_rules', function (Blueprint $table) {
            $table->dropForeign(['run_id']);
        });
        Schema::table('association_rules', function (Blueprint $table) {
            $table->dropColumn('run_id');
        });
        Schema::drop('association_rule_runs');
    }

    public function down(): void
    {
        throw new RuntimeException('Previous analysis runs cannot be recovered. Restore a backup to revert this migration.');
    }
};
