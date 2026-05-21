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
        Schema::create('opportunity_llm_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('input_hash', 64);
            $table->boolean('llm_used')->default(false);
            $table->string('llm_error', 255)->nullable();
            $table->unsignedInteger('llm_duration_ms')->nullable();
            $table->string('model', 64)->nullable();
            $table->string('produce_empanadas', 16)->default('unknown');
            $table->unsignedInteger('estimated_daily_empanadas')->nullable();
            $table->string('intent', 24)->default('unknown');
            $table->decimal('confidence', 5, 4)->nullable();
            $table->text('evidence')->nullable();
            $table->string('next_best_action', 32)->default('wait_for_signal');
            $table->string('recommended_channel', 16)->default('crm');
            $table->string('recommended_sla', 16)->default('esperar');
            $table->text('action_reason')->nullable();
            $table->text('suggested_message')->nullable();
            $table->text('stop_condition')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->unique('customer_id');
            $table->index('input_hash');
            $table->index('llm_used');
            $table->index('analyzed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_llm_analyses');
    }
};
