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
        Schema::create('action_transcriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('action_id')->unique();
            $table->unsignedBigInteger('customer_file_id')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('model', 50)->default('whisper-1');
            $table->string('language', 10)->default('es');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->longText('transcript_text')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_transcriptions');
    }
};
