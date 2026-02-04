<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('actions')) {
            return;
        }

        if (! Schema::hasColumn('actions', 'creation_seconds')) {
            Schema::table('actions', function (Blueprint $table) {
                $table->unsignedInteger('creation_seconds')->nullable()->after('reminder_type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('actions')) {
            return;
        }

        if (Schema::hasColumn('actions', 'creation_seconds')) {
            Schema::table('actions', function (Blueprint $table) {
                $table->dropColumn('creation_seconds');
            });
        }
    }
};
