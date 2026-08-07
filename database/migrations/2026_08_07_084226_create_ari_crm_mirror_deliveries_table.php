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
        Schema::create('ari_crm_mirror_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('ari_crm_mirror_integrations')->cascadeOnDelete();
            $table->uuid('delivery_id');
            $table->string('event', 32);
            $table->uuid('aricrm_message_id');
            $table->string('status', 16)->default('accepted');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->json('payload');
            $table->unsignedBigInteger('wire_message_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['integration_id', 'delivery_id']);
            $table->index(['integration_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ari_crm_mirror_deliveries');
    }
};
