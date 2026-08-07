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
        Schema::create('ari_crm_mirror_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained('ari_crm_mirror_integrations')->cascadeOnDelete();
            $table->uuid('aricrm_message_id');
            $table->unsignedBigInteger('wire_message_id');
            $table->string('wa_message_id')->nullable();
            $table->string('origin', 32)->default('aricrm_mirror');
            $table->string('author_type', 32);
            $table->uuid('author_id')->nullable();
            $table->string('author_name')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->string('media_status', 24)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('wire_message_id')->references('id')->on('wire_messages')->cascadeOnDelete();
            $table->unique(['integration_id', 'aricrm_message_id']);
            $table->index(['integration_id', 'wa_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ari_crm_mirror_messages');
    }
};
