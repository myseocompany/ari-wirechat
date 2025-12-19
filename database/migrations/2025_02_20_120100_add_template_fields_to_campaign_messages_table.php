<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_messages')) {
            return;
        }

        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->string('template_name', 190)->nullable()->after('campaign_id');
            $table->string('template_language', 10)->nullable()->after('template_name');
            $table->string('component', 50)->default('body')->after('template_language');
            $table->unsignedInteger('sequence')->default(1)->after('component');
            $table->string('source')->nullable()->after('sequence');
            $table->string('fallback')->nullable()->after('source');
            $table->json('payload')->nullable()->after('text');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_messages')) {
            return;
        }

        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropColumn([
                'template_name',
                'template_language',
                'component',
                'sequence',
                'source',
                'fallback',
                'payload',
            ]);
        });
    }
};
