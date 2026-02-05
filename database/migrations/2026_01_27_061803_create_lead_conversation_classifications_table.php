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
        Schema::dropIfExists('lead_conversation_classifications');

        Schema::create('lead_conversation_classifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('status', 32);
            $table->unsignedTinyInteger('score')->default(0);
            $table->decimal('confidence', 5, 4)->nullable();
            $table->json('signals_json')->nullable();
            $table->json('reasons_json')->nullable();
            $table->unsignedInteger('suggested_tag_id')->nullable();
            $table->unsignedInteger('applied_tag_id')->nullable();
            $table->timestamp('applied_tag_at')->nullable();
            $table->timestamp('last_customer_message_at')->nullable();
            $table->timestamp('classified_at')->nullable();
            $table->string('classifier_version', 32)->default('v1');
            $table->string('prompt_version', 32)->nullable();
            $table->string('model', 64)->nullable();
            $table->timestamps();

            $table->foreign('suggested_tag_id')
                ->references('id')
                ->on('tags')
                ->nullOnDelete();
            $table->foreign('applied_tag_id')
                ->references('id')
                ->on('tags')
                ->nullOnDelete();

            $table->unique('conversation_id');
            $table->index('customer_id');
            $table->index('status');
            $table->index('score');
            $table->index('last_customer_message_at');
            $table->index('classified_at');
            $table->index('classifier_version');
            $table->index(['status', 'score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_conversation_classifications');
    }
};
