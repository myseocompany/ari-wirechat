<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_message_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('message_source_id'); // Relacionado con message_sources
            $table->boolean('is_active')->default(true); // Para saber si el canal está activo o no
            $table->boolean('is_default')->default(false);  // Columna añadida para marcar el canal predeterminado
            
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('message_source_id')->references('id')->on('message_sources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_message_sources');
    }
};
