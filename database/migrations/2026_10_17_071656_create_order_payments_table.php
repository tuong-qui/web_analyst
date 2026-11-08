<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('order_payments')) {
            return;
        }

        Schema::create('order_payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_0900_ai_ci';
            $table->unsignedBigInteger('dataset_id');
            $table->string('order_id', 50);
            $table->integer('payment_sequential');
            $table->string('payment_type', 30)->nullable();
            $table->integer('payment_installments')->nullable();
            $table->decimal('payment_value', 10, 2)->nullable();
            $table->primary(['dataset_id', 'order_id', 'payment_sequential']);
            $table->foreign('dataset_id')->references('dataset_id')->on('datasets')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign(['dataset_id', 'order_id'])->references(['dataset_id', 'order_id'])->on('orders')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Cannot safely remove payment data. Restore a backup to revert this migration.');
    }
};
