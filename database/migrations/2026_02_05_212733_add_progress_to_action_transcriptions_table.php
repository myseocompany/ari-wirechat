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
        Schema::table('action_transcriptions', function (Blueprint $table) {
            $table->string('progress_step', 50)->nullable()->after('status');
            $table->string('progress_message', 255)->nullable()->after('progress_step');
            $table->unsignedTinyInteger('progress_percent')->nullable()->after('progress_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('action_transcriptions', function (Blueprint $table) {
            $table->dropColumn(['progress_step', 'progress_message', 'progress_percent']);
        });
    }
};
