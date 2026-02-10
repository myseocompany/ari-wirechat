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
        Schema::create('machine_fault_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->string('fault_code', 80);
            $table->string('severity', 20);
            $table->timestamp('reported_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'reported_at']);
            $table->index(['fault_code', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_fault_events');
    }
};
