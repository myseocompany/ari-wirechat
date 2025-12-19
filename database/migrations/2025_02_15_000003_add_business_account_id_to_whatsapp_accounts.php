<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_accounts')) {
            return;
        }

        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->string('business_account_id')->nullable()->after('phone_number_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('whatsapp_accounts')) {
            return;
        }

        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn('business_account_id');
        });
    }
};
