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
        Schema::create('whatsapp_messages_map', function (Blueprint $table) {
            $table->id();
            $table->string('external_message_id')->unique();
            $table->unsignedBigInteger('wire_message_id');
            $table->string('wa_id')->index();
            $table->json('raw_payload');
            $table->timestamps();

            $table->foreign('wire_message_id')
                ->references('id')
                ->on('wire_messages')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages_map');
    }
};
