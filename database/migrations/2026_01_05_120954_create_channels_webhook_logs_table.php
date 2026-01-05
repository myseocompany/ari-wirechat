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
        Schema::create('channels_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->json('payload')->nullable();
            $table->text('payload_raw')->nullable();
            $table->json('headers')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('route', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels_webhook_logs');
    }
};
