<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->json('filters')->nullable()->after('audience_id');
            $table->unsignedInteger('max_recipients')->nullable()->after('filters');
            $table->foreignId('whatsapp_account_id')
                ->nullable()
                ->after('max_recipients')
                ->constrained('whatsapp_accounts')
                ->nullOnDelete();
            $table->foreignId('whatsapp_template_id')
                ->nullable()
                ->after('whatsapp_account_id')
                ->constrained('whatsapp_templates')
                ->nullOnDelete();
            $table->string('template_name', 190)->nullable()->after('whatsapp_template_id');
            $table->string('template_language', 10)->nullable()->after('template_name');
            $table->string('header_type', 20)->default('none')->after('template_language');
            $table->string('header_media_url', 500)->nullable()->after('header_type');
            $table->unsignedInteger('wait_seconds')->default(30)->after('header_media_url');
            $table->string('action_note', 255)->nullable()->after('wait_seconds');
            $table->json('settings')->nullable()->after('action_note');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['whatsapp_account_id']);
            $table->dropForeign(['whatsapp_template_id']);
            $table->dropColumn([
                'description',
                'filters',
                'max_recipients',
                'whatsapp_account_id',
                'whatsapp_template_id',
                'template_name',
                'template_language',
                'header_type',
                'header_media_url',
                'wait_seconds',
                'action_note',
                'settings',
            ]);
        });
    }
};
