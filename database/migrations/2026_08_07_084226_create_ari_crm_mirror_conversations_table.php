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
        Schema::create('ari_crm_mirror_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('ari_crm_mirror_integrations')->cascadeOnDelete();
            $table->uuid('aricrm_conversation_id');
            $table->unsignedBigInteger('conversation_id');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('wire_conversations')->cascadeOnDelete();
            $table->unique(['integration_id', 'aricrm_conversation_id'], 'ari_crm_mirror_conversation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ari_crm_mirror_conversations');
    }
};
