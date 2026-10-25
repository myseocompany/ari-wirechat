<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade `wa_id` (identidad de WhatsApp: LID tipo "CO.1766162541355187" o sus
     * dígitos) a `customers`, para que el espejo de AriCRM deje de guardar el LID
     * dentro de `phone`.
     *
     * Backfill: los customers creados por el espejo con un LID de 16+ dígitos en
     * `phone` (un teléfono E.164 real nunca supera 15 dígitos) se mueven a
     * `wa_id` y `phone` queda en NULL. `phone_last9` es columna generada y se
     * recalcula sola.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'wa_id')) {
                $column = $table->string('wa_id', 64)->nullable();

                if (Schema::hasColumn('customers', 'phone')) {
                    $column->after('phone');
                }

                $table->index('wa_id');
            }
        });

        if (Schema::hasTable('ari_crm_mirror_contacts')) {
            DB::statement('UPDATE customers c JOIN ari_crm_mirror_contacts m ON m.customer_id = c.id SET c.wa_id = c.phone WHERE c.wa_id IS NULL AND c.phone IS NOT NULL AND CHAR_LENGTH(c.phone) > 15');
            DB::statement('UPDATE customers c JOIN ari_crm_mirror_contacts m ON m.customer_id = c.id SET c.phone = NULL WHERE c.wa_id = c.phone AND CHAR_LENGTH(c.wa_id) > 15');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'wa_id')) {
                $table->dropIndex(['wa_id']);
                $table->dropColumn('wa_id');
            }
        });
    }
};
