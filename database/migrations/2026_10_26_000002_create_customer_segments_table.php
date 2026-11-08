<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The same table can be installed manually through phpMyAdmin.
        if (Schema::hasTable('customer_segments')) {
            return;
        }

        Schema::create('customer_segments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('dataset_id');
            $table->date('analysis_date');
            $table->string('customer_unique_id', 50);
            $table->date('last_purchase');
            $table->unsignedInteger('recency');
            $table->unsignedBigInteger('frequency');
            $table->decimal('monetary', 20, 2);
            $table->unsignedTinyInteger('r_score');
            $table->unsignedTinyInteger('f_score');
            $table->unsignedTinyInteger('m_score');
            $table->string('segment', 20);
            $table->timestamp('analyzed_at');
            $table->primary(['dataset_id', 'analysis_date', 'customer_unique_id'], 'customer_segments_primary');
            $table->index(['dataset_id', 'analysis_date', 'segment'], 'customer_segments_group_index');
            $table->foreign('dataset_id', 'customer_segments_dataset_foreign')
                ->references('dataset_id')->on('datasets')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_segments');
    }
};
