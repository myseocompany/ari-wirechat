<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const POE_CAMPAIGN_ID = 40;

    private const GROUP_ID = 4000;

    /**
     * Orden de los grupos del formulario POE. El grupo nuevo (4000) queda entre
     * "Información de destino" (28) y "Veracidad de la información" (34).
     */
    private const GROUP_WEIGHTS = [
        14 => 10,
        20 => 20,
        28 => 30,
        self::GROUP_ID => 35,
        34 => 40,
    ];

    /**
     * Agrega al formulario POE (campaña 40) la pregunta de residencia en la
     * Unión Europea o Reino Unido y la pregunta sobre el número EORI.
     *
     * `customer_meta_datas` y `campaign_customer_meta_data` no tienen migración
     * propia en el repo (existen solo en producción), así que todo se protege con
     * hasTable. Los IDs son fijos (bloque 4000) porque la vista del formulario los
     * referencia, siguiendo la convención de las campañas 44 (2001+) y 45 (3000).
     */
    public function up(): void
    {
        if (! Schema::hasTable('customer_meta_datas')) {
            return;
        }

        $now = now();

        foreach ($this->metaDataRows() as $row) {
            DB::table('customer_meta_datas')->updateOrInsert(
                ['id' => $row['id']],
                $row + ['created_at' => $now, 'updated_at' => $now]
            );
        }

        foreach (self::GROUP_WEIGHTS as $id => $weight) {
            DB::table('customer_meta_datas')->where('id', $id)->update(['weight' => $weight]);
        }

        if (! Schema::hasTable('campaign_customer_meta_data') || ! $this->poeCampaignExists()) {
            return;
        }

        $linked = DB::table('campaign_customer_meta_data')
            ->where('campaign_id', self::POE_CAMPAIGN_ID)
            ->where('customer_meta_data_id', self::GROUP_ID)
            ->exists();

        if (! $linked) {
            DB::table('campaign_customer_meta_data')->insert([
                'campaign_id' => self::POE_CAMPAIGN_ID,
                'customer_meta_data_id' => self::GROUP_ID,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Quita las preguntas del formulario. Las respuestas ya guardadas en
     * `customer_metas` se conservan.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customer_meta_datas')) {
            return;
        }

        if (Schema::hasTable('campaign_customer_meta_data')) {
            DB::table('campaign_customer_meta_data')
                ->where('customer_meta_data_id', self::GROUP_ID)
                ->delete();
        }

        DB::table('customer_meta_datas')
            ->whereIn('id', array_column($this->metaDataRows(), 'id'))
            ->delete();

        DB::table('customer_meta_datas')
            ->whereIn('id', array_keys(self::GROUP_WEIGHTS))
            ->update(['weight' => null]);
    }

    private function poeCampaignExists(): bool
    {
        return Schema::hasTable('campaigns')
            && DB::table('campaigns')->where('id', self::POE_CAMPAIGN_ID)->exists();
    }

    /**
     * @return array<int, array{id: int, parent_id: int|null, value: string, type_id: int|null, weight: int|null}>
     */
    private function metaDataRows(): array
    {
        return [
            ['id' => self::GROUP_ID, 'parent_id' => null, 'value' => 'Residencia y número EORI', 'type_id' => null, 'weight' => 35],
            ['id' => 4001, 'parent_id' => self::GROUP_ID, 'value' => '¿Es usted residente de la Unión Europea o Reino Unido?', 'type_id' => 2, 'weight' => 1],
            ['id' => 4002, 'parent_id' => self::GROUP_ID, 'value' => '¿Cuenta usted con un número EORI?', 'type_id' => 2, 'weight' => 2],
            ['id' => 4003, 'parent_id' => 4001, 'value' => 'Sí', 'type_id' => null, 'weight' => 1],
            ['id' => 4004, 'parent_id' => 4001, 'value' => 'No', 'type_id' => null, 'weight' => 2],
            ['id' => 4005, 'parent_id' => 4002, 'value' => 'Sí, cuento con número EORI.', 'type_id' => null, 'weight' => 1],
            ['id' => 4006, 'parent_id' => 4002, 'value' => 'No, aún no cuento con número EORI.', 'type_id' => null, 'weight' => 2],
        ];
    }
};
