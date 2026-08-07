<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('ari_crm_mirror_contacts', ['integration_id', 'aricrm_contact_id'], 'unique')) {
            Schema::table('ari_crm_mirror_contacts', function (Blueprint $table) {
                $table->unique(['integration_id', 'aricrm_contact_id']);
            });
        }

        if (! Schema::hasIndex('ari_crm_mirror_contacts', ['integration_id', 'normalized_phone'])) {
            Schema::table('ari_crm_mirror_contacts', function (Blueprint $table) {
                $table->index(['integration_id', 'normalized_phone']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('ari_crm_mirror_contacts', ['integration_id', 'normalized_phone'])) {
            Schema::table('ari_crm_mirror_contacts', function (Blueprint $table) {
                $table->dropIndex(['integration_id', 'normalized_phone']);
            });
        }

        if (Schema::hasIndex('ari_crm_mirror_contacts', ['integration_id', 'aricrm_contact_id'], 'unique')) {
            Schema::table('ari_crm_mirror_contacts', function (Blueprint $table) {
                $table->dropUnique(['integration_id', 'aricrm_contact_id']);
            });
        }
    }
};
