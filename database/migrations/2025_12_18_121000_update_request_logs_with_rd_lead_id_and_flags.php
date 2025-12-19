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
        if (! Schema::hasTable('request_logs')) {
            return;
        }

        Schema::table('request_logs', function (Blueprint $table) {
            $table->string('rd_lead_id')->nullable()->after('facebook_id')->index();
            $table->boolean('ignored')->default(false)->after('rd_lead_id')->index();
            $table->string('ignore_reason')->nullable()->after('ignored');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('request_logs')) {
            return;
        }

        Schema::table('request_logs', function (Blueprint $table) {
            if (Schema::hasColumn('request_logs', 'rd_lead_id')) {
                $table->dropIndex(['rd_lead_id']);
                $table->dropColumn('rd_lead_id');
            }
            if (Schema::hasColumn('request_logs', 'ignored')) {
                $table->dropIndex(['ignored']);
                $table->dropColumn('ignored');
            }
            if (Schema::hasColumn('request_logs', 'ignore_reason')) {
                $table->dropColumn('ignore_reason');
            }
        });
    }
};
