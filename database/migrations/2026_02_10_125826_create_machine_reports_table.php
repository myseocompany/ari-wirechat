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
        Schema::create('machine_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->string('batch_id', 64)->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('received_at');
            $table->json('payload_json');
            $table->longText('raw_body');
            $table->string('signature', 128)->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'reported_at']);
            $table->index(['machine_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_reports');
    }
};
