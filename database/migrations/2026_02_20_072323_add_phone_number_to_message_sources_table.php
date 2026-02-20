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
        if (! Schema::hasTable('message_sources') || Schema::hasColumn('message_sources', 'phone_number')) {
            return;
        }

        Schema::table('message_sources', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('APIKEY');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('message_sources') || ! Schema::hasColumn('message_sources', 'phone_number')) {
            return;
        }

        Schema::table('message_sources', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};
