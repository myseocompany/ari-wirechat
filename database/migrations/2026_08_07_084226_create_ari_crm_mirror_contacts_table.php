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
        Schema::create('ari_crm_mirror_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('ari_crm_mirror_integrations')->cascadeOnDelete();
            $table->uuid('aricrm_contact_id');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('normalized_phone', 40)->nullable();
            $table->timestamps();

            $table->unique(['integration_id', 'aricrm_contact_id']);
            $table->index(['integration_id', 'normalized_phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ari_crm_mirror_contacts');
    }
};
