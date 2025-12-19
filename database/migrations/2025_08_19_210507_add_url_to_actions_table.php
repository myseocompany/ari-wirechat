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
        if (! Schema::hasTable('actions')) {
            return;
        }

        Schema::table('actions', function (Blueprint $table) {
            $table->string('url')->nullable()->after('note');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('actions')) {
            return;
        }

        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};
