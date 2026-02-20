<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Namu\WireChat\Models\Conversation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('message_source_conversations');

        Schema::create('message_source_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_source_id')->constrained('message_sources')->cascadeOnDelete();
            $table->integer('customer_id');
            $table->unsignedBigInteger('conversation_id');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();

            $table->foreign('conversation_id')
                ->references('id')
                ->on((new Conversation)->getTable())
                ->cascadeOnDelete();

            $table->unique(['message_source_id', 'customer_id'], 'msg_source_customer_unique');
            $table->unique('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_source_conversations');
    }
};
