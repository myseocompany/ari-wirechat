<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $newCustomerStatusId = DB::table('customer_statuses')
            ->where('name', 'Nuevo')
            ->value('id');

        if (! $newCustomerStatusId) {
            throw new RuntimeException('No se encontró el estado inicial "Nuevo" para clientes de AriCRM.');
        }

        DB::table('customers')
            ->whereNull('status_id')
            ->whereIn('id', function ($query): void {
                $query->select('customer_id')
                    ->from('ari_crm_mirror_contacts');
            })
            ->update(['status_id' => $newCustomerStatusId]);
    }

    public function down(): void {}
};
