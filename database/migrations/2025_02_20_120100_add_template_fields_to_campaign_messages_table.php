<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->string('component', 50)->default('body')->after('campaign_id');
            $table->unsignedInteger('sequence')->default(1)->after('component');
            $table->string('source')->nullable()->after('sequence');
            $table->string('fallback')->nullable()->after('source');
            $table->json('payload')->nullable()->after('text');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropColumn([
                'component',
                'sequence',
                'source',
                'fallback',
                'payload',
            ]);
        });
    }
};
