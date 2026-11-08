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
        Schema::table('sessions', function (Blueprint $table) {
            if (DB::table('sessions')->whereNotNull('user_id')
                ->whereNotIn('user_id', DB::table('users')->select('user_id'))->exists()) {
                throw new RuntimeException('Some sessions reference missing users. Resolve these sessions before adding the foreign key.');
            }

            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE sessions ENGINE = InnoDB');
                Schema::table('sessions', function (Blueprint $table) {
                    $table->integer('user_id')->nullable()->change();
                });
            }

            Schema::table('sessions', function (Blueprint $table) {
                $table->foreign('user_id', 'sessions_user_id_foreign')->references('user_id')->on('users')
                    ->cascadeOnDelete()->cascadeOnUpdate();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropForeign('sessions_user_id_foreign');
            });

            if (DB::getDriverName() === 'mysql') {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                });
            }
        });
    }
};
