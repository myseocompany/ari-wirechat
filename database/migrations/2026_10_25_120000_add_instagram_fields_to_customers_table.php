<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade los campos de Instagram a `customers`:
     * - instagram_url varchar(255) nullable — handle o URL del perfil del prospecto
     * - instagram_followers bigint unsigned nullable — snapshot manual de seguidores
     *
     * Ambas se crean de forma condicional: `instagram_url` ya fue añadida a mano en
     * producción, así que la migración debe poder correr sobre esa base sin fallar.
     * Columnas nullable, sin default y sin índice: en una tabla de ~147k filas el
     * ADD COLUMN es instantáneo y no reescribe la tabla.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'instagram_url')) {
                $column = $table->string('instagram_url', 255)->nullable();

                if (Schema::hasColumn('customers', 'linkedin_url')) {
                    $column->after('linkedin_url');
                }
            }

            if (! Schema::hasColumn('customers', 'instagram_followers')) {
                $column = $table->unsignedBigInteger('instagram_followers')->nullable();

                if (Schema::hasColumn('customers', 'instagram_url')) {
                    $column->after('instagram_url');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Ojo: esto también elimina `instagram_url` y los datos ya cargados a mano
     * en producción. Hacer rollback solo como decisión explícita.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            foreach (['instagram_followers', 'instagram_url'] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
