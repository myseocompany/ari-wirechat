<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_account_id');
            $table->string('name');
            $table->string('language')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
