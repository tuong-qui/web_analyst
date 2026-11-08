<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->json('recommendation_context')->nullable();
        });

        DB::table('datasets')->eachById(function ($dataset) {
            $latest = DB::table('recommendation_runs')->where('dataset_id', $dataset->dataset_id)
                ->orderByDesc('id')->first();
            if ($latest !== null) {
                DB::table('bundle_recommendations')->where('dataset_id', $dataset->dataset_id)
                    ->where(fn ($query) => $query->whereNull('recommendation_run_id')
                        ->orWhere('recommendation_run_id', '<>', $latest->id))->delete();
                DB::table('datasets')->where('dataset_id', $dataset->dataset_id)->update([
                    'recommendation_context' => json_encode([
                        'items' => json_decode($latest->selected_items, true),
                    ]),
                ]);
            }
        }, 100, 'dataset_id');

        Schema::table('bundle_recommendations', function (Blueprint $table) {
            $table->dropForeign(['recommendation_run_id']);
        });
        Schema::table('bundle_recommendations', function (Blueprint $table) {
            $table->dropColumn('recommendation_run_id');
        });
        Schema::drop('recommendation_runs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Previous recommendation runs cannot be recovered. Restore a backup to revert this migration.');
    }
};
