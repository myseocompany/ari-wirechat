<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            // Customers.id is a signed INT in this project, so match the signed type here.
            $table->integer('customer_id')->nullable();
            $table->integer('quiz_meta_id')->nullable();
            $table->string('name')->nullable();
            $table->string('stage')->nullable();
            $table->decimal('final_score', 8, 2)->nullable();
            $table->string('completed_at')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
