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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE order_payments ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');
        $foreignKeys = collect(Schema::getForeignKeys('order_payments'))->pluck('name');
        Schema::table('order_payments', function (Blueprint $table) use ($foreignKeys) {
            if (! $foreignKeys->contains('order_payments_dataset_id_foreign')) {
                $table->foreign('dataset_id')->references('dataset_id')->on('datasets')
                    ->restrictOnDelete()->cascadeOnUpdate();
            }
            if (! $foreignKeys->contains('order_payments_dataset_id_order_id_foreign')) {
                $table->foreign(['dataset_id', 'order_id'])->references(['dataset_id', 'order_id'])->on('orders')
                    ->restrictOnDelete()->cascadeOnUpdate();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Cannot safely remove payment constraints. Restore a backup to revert this migration.');
    }
};
