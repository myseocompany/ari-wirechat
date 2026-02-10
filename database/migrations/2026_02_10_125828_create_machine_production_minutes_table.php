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
        Schema::create('machine_production_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->timestamp('minute_at');
            $table->unsignedBigInteger('tacometer_total');
            $table->integer('units_in_minute');
            $table->boolean('is_backfill')->default(false);
            $table->timestamp('received_at');
            $table->timestamps();

            $table->unique(['machine_id', 'minute_at']);
            $table->index(['machine_id', 'minute_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_production_minutes');
    }
};
