<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels_call_recoveries', function (Blueprint $table) {
            $table->id();
            $table->string('call_id', 191)->unique();
            $table->timestamp('call_created_at')->nullable()->index();
            $table->string('msisdn', 64)->nullable()->index();
            $table->string('agent_id', 64)->nullable();
            $table->boolean('recording_exists')->default(false);
            $table->text('recording_url')->nullable();
            $table->string('status', 32)->default('queued')->index();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->string('local_file_path', 500)->nullable();
            $table->unsignedBigInteger('local_file_size')->nullable();
            $table->text('error')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels_call_recoveries');
    }
};
