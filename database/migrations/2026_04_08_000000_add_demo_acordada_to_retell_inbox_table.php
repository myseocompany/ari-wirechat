<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->boolean('demo_acordada')->nullable()->after('live_attendance_status');
        });
    }

    public function down(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->dropColumn('demo_acordada');
        });
    }
};
