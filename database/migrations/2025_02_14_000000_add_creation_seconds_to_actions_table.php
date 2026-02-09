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
                $column = $table->unsignedInteger('creation_seconds')->nullable();
                if (Schema::hasColumn('actions', 'reminder_type')) {
                    $column->after('reminder_type');
                }
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
