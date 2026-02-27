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
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'can_view_all_customers')) {
                $table->boolean('can_view_all_customers')
                    ->default(false)
                    ->after('name');
            }
        });

        DB::table('roles')
            ->whereIn('id', [1, 2, 10, 11, 12, 14, 15])
            ->update(['can_view_all_customers' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'can_view_all_customers')) {
                $table->dropColumn('can_view_all_customers');
            }
        });
    }
};
