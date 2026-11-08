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
            throw new RuntimeException('This upgrade targets the existing MySQL olist_db schema.');
        }
        $keys = [
            'customers' => ['customer_id'],
            'products' => ['product_id'],
            'orders' => ['order_id'],
            'order_items' => ['order_id', 'order_item_id'],
            'order_payments' => ['order_id', 'payment_sequential'],
        ];
        $tables = [...array_keys($keys), 'order_reviews', 'association_rules', 'bundle_recommendations', 'inventory_alerts'];
        $foreignKeys = [];
        foreach ($tables as $name) {
            foreach (Schema::getForeignKeys($name) as $foreign) {
                if (in_array($foreign['foreign_table'], ['customers', 'products', 'orders'], true)) {
                    $foreignKeys[$name][] = $foreign;
                }
            }
        }
        Schema::table('datasets', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
        });
        $legacyId = DB::table('datasets')->insertGetId([
            'user_id' => null,
            'name' => 'Dữ liệu cũ chưa xác định chủ sở hữu',
            'description' => 'Chỉ gán chủ sở hữu sau khi xác minh nguồn dữ liệu.',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'dataset_id');
        foreach ($tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->unsignedBigInteger('dataset_id')->nullable();
            });
            DB::table($name)->update(['dataset_id' => $legacyId]);
            Schema::table($name, function (Blueprint $table) {
                $table->unsignedBigInteger('dataset_id')->nullable(false)->change();
                $table->foreign('dataset_id')->references('dataset_id')->on('datasets')->restrictOnDelete()->cascadeOnUpdate();
            });
        }
        foreach ($foreignKeys as $name => $constraints) {
            foreach ($constraints as $foreign) {
                Schema::table($name, fn (Blueprint $table) => $table->dropForeign($foreign['name']));
            }
        }
        foreach ($keys as $name => $columns) {
            Schema::table($name, function (Blueprint $table) use ($columns) {
                $table->dropPrimary();
                $table->primary(['dataset_id', ...$columns]);
            });
        }
        foreach ($foreignKeys as $name => $constraints) {
            foreach ($constraints as $foreign) {
                Schema::table($name, function (Blueprint $table) use ($foreign) {
                    $table->foreign(['dataset_id', ...$foreign['columns']], $foreign['name'].'_dataset')
                        ->references(['dataset_id', ...$foreign['foreign_columns']])
                        ->on($foreign['foreign_table'])->restrictOnDelete()->cascadeOnUpdate();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Cannot safely merge datasets with overlapping business IDs. Restore a backup to revert this upgrade.');
    }
};
