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
        Schema::table('lead_conversation_classifications', function (Blueprint $table) {
            $table->boolean('llm_used')->default(false)->after('model');
            $table->string('llm_error', 255)->nullable()->after('llm_used');
            $table->unsignedInteger('llm_duration_ms')->nullable()->after('llm_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_conversation_classifications', function (Blueprint $table) {
            $table->dropColumn(['llm_used', 'llm_error', 'llm_duration_ms']);
        });
    }
};
