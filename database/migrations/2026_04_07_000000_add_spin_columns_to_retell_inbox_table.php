<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->string('escenario_detectado', 100)->nullable()->after('live_attendance_status');
            $table->boolean('hizo_apertura_correcta')->nullable()->after('escenario_detectado');
            $table->boolean('preguntas_situacion')->nullable()->after('hizo_apertura_correcta');
            $table->boolean('identifico_problema')->nullable()->after('preguntas_situacion');
            $table->boolean('hizo_implicacion')->nullable()->after('identifico_problema');
            $table->boolean('cliente_dijo_beneficio')->nullable()->after('hizo_implicacion');
            $table->boolean('cerro_con_paso_concreto')->nullable()->after('cliente_dijo_beneficio');
            $table->unsignedTinyInteger('puntaje_spin')->nullable()->after('cerro_con_paso_concreto');
            $table->text('resumen_llamada')->nullable()->after('puntaje_spin');
            $table->text('principal_error')->nullable()->after('resumen_llamada');
            $table->text('recomendacion')->nullable()->after('principal_error');

            $table->index('escenario_detectado');
            $table->index('puntaje_spin');
            $table->index('hizo_implicacion');
        });
    }

    public function down(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->dropIndex(['escenario_detectado']);
            $table->dropIndex(['puntaje_spin']);
            $table->dropIndex(['hizo_implicacion']);
            $table->dropColumn([
                'escenario_detectado',
                'hizo_apertura_correcta',
                'preguntas_situacion',
                'identifico_problema',
                'hizo_implicacion',
                'cliente_dijo_beneficio',
                'cerro_con_paso_concreto',
                'puntaje_spin',
                'resumen_llamada',
                'principal_error',
                'recomendacion',
            ]);
        });
    }
};
