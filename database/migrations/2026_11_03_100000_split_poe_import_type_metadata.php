<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FIELD_ID = 4007;

    private const LEGACY_ID = 34;

    /**
     * El select "Importación de manera" del POE se guardaba bajo el id 34, que es el
     * grupo "Veracidad de la información"; en el POA se veía con la etiqueta equivocada.
     * Se crea su propio campo (4007) y se reasignan las respuestas históricas.
     * El campo se dibuja aparte en la vista, por eso no se vincula a la campaña.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customer_meta_datas')) {
            return;
        }

        $now = now();

        DB::table('customer_meta_datas')->updateOrInsert(
            ['id' => self::FIELD_ID],
            [
                'parent_id' => DB::table('customer_meta_datas')->where('id', self::LEGACY_ID)->value('parent_id'),
                'value' => 'Importación de manera',
                'type_id' => 2,
                'weight' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        if (Schema::hasTable('customer_metas')) {
            DB::table('customer_metas')
                ->where('meta_data_id', self::LEGACY_ID)
                ->whereIn('value', \App\Models\CustomerMetaData::POE_IMPORT_TYPES)
                ->update(['meta_data_id' => self::FIELD_ID]);
        }
    }

    /**
     * Devuelve las respuestas al id 34 (donde las guarda el formulario anterior)
     * y quita el campo. No borra respuestas.
     */
    public function down(): void
    {
        if (Schema::hasTable('customer_metas')) {
            DB::table('customer_metas')
                ->where('meta_data_id', self::FIELD_ID)
                ->update(['meta_data_id' => self::LEGACY_ID]);
        }

        if (Schema::hasTable('customer_meta_datas')) {
            DB::table('customer_meta_datas')->where('id', self::FIELD_ID)->delete();
        }
    }
};
