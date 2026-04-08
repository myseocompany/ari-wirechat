<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->string('agent_id', 100)->nullable()->after('call_id');
            $table->string('agent_name', 150)->nullable()->after('agent_id');
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->dropIndex(['agent_id']);
            $table->dropColumn(['agent_id', 'agent_name']);
        });
    }
};
