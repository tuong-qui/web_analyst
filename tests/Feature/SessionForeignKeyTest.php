<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionForeignKeyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('payload');
        });
        DB::table('users')->insert(['user_id' => 1]);
        DB::table('sessions')->insert([
            ['id' => 'signed-in', 'user_id' => 1, 'payload' => 'existing'],
            ['id' => 'guest', 'user_id' => null, 'payload' => 'guest'],
        ]);
    }

    public function test_migration_preserves_sessions_and_removes_user_sessions_on_account_deletion(): void
    {
        (require database_path('migrations/2026_10_29_000002_add_user_foreign_key_to_sessions_table.php'))->up();

        $this->assertDatabaseHas('sessions', ['id' => 'signed-in', 'user_id' => 1, 'payload' => 'existing']);
        DB::table('users')->where('user_id', 1)->delete();

        $this->assertDatabaseMissing('sessions', ['id' => 'signed-in']);
        $this->assertDatabaseHas('sessions', ['id' => 'guest', 'user_id' => null]);
    }

    public function test_sessions_cannot_reference_a_missing_user(): void
    {
        (require database_path('migrations/2026_10_29_000002_add_user_foreign_key_to_sessions_table.php'))->up();

        $this->expectException(QueryException::class);
        DB::table('sessions')->insert(['id' => 'invalid', 'user_id' => 99, 'payload' => 'invalid']);
    }

    public function test_migration_refuses_orphans_without_deleting_sessions(): void
    {
        DB::table('sessions')->insert(['id' => 'orphan', 'user_id' => 99, 'payload' => 'preserved']);

        try {
            (require database_path('migrations/2026_10_29_000002_add_user_foreign_key_to_sessions_table.php'))->up();
            $this->fail('Migration must reject sessions referencing missing users.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('missing users', $exception->getMessage());
        }

        $this->assertDatabaseHas('sessions', ['id' => 'orphan', 'payload' => 'preserved']);
        $this->assertDatabaseCount('sessions', 3);
    }
}
