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
        Schema::create('ari_crm_mirror_integrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('aricrm_tenant_id')->unique();
            $table->foreignId('message_source_id')->constrained()->cascadeOnDelete();
            $table->text('secret_current');
            $table->text('secret_previous')->nullable();
            $table->timestamp('previous_secret_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ari_crm_mirror_integrations');
    }
};
