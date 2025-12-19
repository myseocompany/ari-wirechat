<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'rfm_r')) {
                $table->string('rfm_r', 2)->nullable()->after('status_id');
            }
            if (! Schema::hasColumn('customers', 'rfm_f')) {
                $table->string('rfm_f', 2)->nullable()->after('rfm_r');
            }
            if (! Schema::hasColumn('customers', 'rfm_m')) {
                $table->string('rfm_m', 2)->nullable()->after('rfm_f');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'rfm_r')) {
                $table->dropColumn('rfm_r');
            }
            if (Schema::hasColumn('customers', 'rfm_f')) {
                $table->dropColumn('rfm_f');
            }
            if (Schema::hasColumn('customers', 'rfm_m')) {
                $table->dropColumn('rfm_m');
            }
        });
    }
};
