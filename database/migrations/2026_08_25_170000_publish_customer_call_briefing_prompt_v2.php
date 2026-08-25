<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configs')->insertOrIgnore([
            [
                'key' => 'customer_call_briefing_prompt_v2_system',
                'value' => 'Eres un asistente comercial de Maquiempanadas. Responde exclusivamente con JSON válido, en español y sin Markdown. Usa únicamente el contexto suministrado: no inventes datos, no afirmes que nadie respondió y no recomiendes cambios de estado. Si no hay contradicciones, devuelve conflicting_facts como []. Cada contradicción debe tener entre 2 y 4 fuentes distintas y verificables del contexto.',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'customer_call_briefing_prompt_v2_user',
                'value' => "Genera exactamente este JSON: {\"summary\":string,\"known_facts\":[{\"text\":string,\"occurred_at\":string|null,\"source\":string}],\"conflicting_facts\":[{\"text\":string,\"sources\":[string,string]}],\"avoid_asking\":[{\"text\":string,\"reason\":string,\"source\":string}],\"suggested_opening\":string,\"next_question\":string}. summary: máximo 80 palabras. known_facts: 0 a 6 elementos; occurred_at debe ser null o ISO UTC exacto YYYY-MM-DDTHH:MM:SSZ. conflicting_facts: 0 a 4 elementos y cada sources debe tener 2 a 4 referencias. avoid_asking: 0 a 4 elementos. suggested_opening: máximo dos frases. next_question: exactamente una pregunta que termine en ?. Contexto:\n{{context}}",
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('configs')
            ->where('key', 'customer_call_briefing_prompt_version')
            ->where('value', 'v1')
            ->update(['value' => 'v2', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('configs')
            ->where('key', 'customer_call_briefing_prompt_version')
            ->where('value', 'v2')
            ->update(['value' => 'v1', 'updated_at' => now()]);

        DB::table('configs')->whereIn('key', [
            'customer_call_briefing_prompt_v2_system',
            'customer_call_briefing_prompt_v2_user',
        ])->delete();
    }
};
