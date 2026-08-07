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
        Schema::create('ari_crm_mirror_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('ari_crm_mirror_integrations')->cascadeOnDelete();
            $table->string('actor_type', 32);
            $table->uuid('aricrm_actor_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['integration_id', 'actor_type', 'aricrm_actor_id'], 'ari_crm_mirror_actor_identity_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ari_crm_mirror_actors');
    }
};
