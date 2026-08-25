<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_call_briefings', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->primary();
            $table->string('status', 24)->default('pending');
            $table->text('summary')->nullable();
            $table->json('known_facts')->nullable();
            $table->json('conflicting_facts')->nullable();
            $table->json('avoid_asking')->nullable();
            $table->text('suggested_opening')->nullable();
            $table->text('next_question')->nullable();
            $table->char('source_hash', 64)->nullable();
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('model', 100)->nullable();
            $table->string('prompt_version', 20)->default('');
            $table->char('prompt_hash', 64)->default('');
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->index(['status', 'updated_at']);
        });

        DB::table('configs')->insertOrIgnore([
            [
                'key' => 'customer_call_briefing_enabled',
                'value' => '0',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'customer_call_briefing_prompt_version',
                'value' => 'v1',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'customer_call_briefing_prompt_v1_system',
                'value' => 'Eres un asistente comercial de Maquiempanadas. Responde solo JSON valido en espanol. Usa exclusivamente el contexto suministrado. No inventes datos, no afirmes que nadie respondio y no recomiendes cambiar estados del CRM.',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'customer_call_briefing_prompt_v1_user',
                'value' => "Genera exactamente este objeto JSON: {\"summary\":string,\"known_facts\":[{\"text\":string,\"occurred_at\":string|null,\"source\":string}],\"conflicting_facts\":[{\"text\":string,\"sources\":[string]}],\"avoid_asking\":[{\"text\":string,\"reason\":string,\"source\":string}],\"suggested_opening\":string,\"next_question\":string}. Maximos: 6 hechos, 4 contradicciones y 4 preguntas a evitar. La apertura debe retomar el hito comercial mas reciente si existe. Contexto:\n{{context}}",
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configs')->whereIn('key', [
            'customer_call_briefing_enabled',
            'customer_call_briefing_prompt_version',
            'customer_call_briefing_prompt_v1_system',
            'customer_call_briefing_prompt_v1_user',
        ])->delete();

        Schema::dropIfExists('customer_call_briefings');
    }
};
