<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatasetMigrationTest extends TestCase
{
    public function test_mysql_upgrade_preserves_legacy_rows_and_enforces_dataset_relationships(): void
    {
        if (getenv('RUN_MYSQL_MIGRATION_TEST') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_MIGRATION_TEST=1 to test in an isolated temporary MySQL database.');
        }
        $database = 'olist_isolation_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mysql.database' => null, 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
        $server = DB::connection('mysql');
        $server->statement('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');
        config(['database.connections.isolation_test' => array_replace(config('database.connections.mysql'), ['database' => $database, 'url' => null]), 'database.default' => 'isolation_test']);
        try {
            DB::unprepared(file_get_contents(base_path('tests/olist_legacy_schema.sql')));
            DB::table('customers')->insert(['customer_id' => 'shared', 'customer_unique_id' => 'unique']);
            DB::table('products')->insert(['product_id' => 'shared-product']);
            DB::table('orders')->insert(['order_id' => 'shared-order', 'customer_id' => 'shared']);
            DB::table('order_items')->insert(['order_id' => 'shared-order', 'order_item_id' => 1, 'product_id' => 'shared-product']);
            $migration = require database_path('migrations/2026_10_14_151557_scope_business_data_to_datasets.php');
            $migration->up();
            $legacy = DB::table('datasets')->whereNull('user_id')->value('dataset_id');
            foreach (['customers', 'products', 'orders', 'order_items'] as $table) {
                $this->assertSame(1, DB::table($table)->where('dataset_id', $legacy)->count());
            }
            $user = DB::table('users')->insertGetId(['shop_name' => 'Test shop', 'owner_name' => 'Test owner', 'email' => 'isolation@example.test', 'password_hash' => 'test-only'], 'user_id');
            $dataset = DB::table('datasets')->insertGetId(['user_id' => $user, 'name' => 'Owned dataset'], 'dataset_id');
            DB::table('customers')->insert(['dataset_id' => $dataset, 'customer_id' => 'shared', 'customer_unique_id' => 'new-unique']);
            DB::table('products')->insert(['dataset_id' => $dataset, 'product_id' => 'shared-product']);
            DB::table('orders')->insert(['dataset_id' => $dataset, 'order_id' => 'shared-order', 'customer_id' => 'shared']);
            DB::table('order_items')->insert(['dataset_id' => $dataset, 'order_id' => 'shared-order', 'order_item_id' => 1, 'product_id' => 'shared-product']);
            $this->assertSame(2, DB::table('order_items')->count());
            DB::table('products')->insert(['dataset_id' => $legacy, 'product_id' => 'legacy-only']);
            try {
                DB::table('order_items')->insert(['dataset_id' => $dataset, 'order_id' => 'shared-order', 'order_item_id' => 2, 'product_id' => 'legacy-only']);
                $this->fail('A product from a different dataset was accepted.');
            } catch (QueryException $exception) {
                $this->assertSame('23000', $exception->errorInfo[0]);
            }
        } finally {
            DB::disconnect('isolation_test');
            config(['database.default' => 'sqlite']);
            if (preg_match('/^olist_isolation_test_[a-f0-9]{16}$/', $database)) {
                $server->statement('DROP DATABASE `'.$database.'`');
            }
        }
    }
}
