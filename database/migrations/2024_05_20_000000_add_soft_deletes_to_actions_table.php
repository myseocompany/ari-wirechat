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

        if (! Schema::hasColumn('actions', 'deleted_at')) {
            Schema::table('actions', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('actions')) {
            return;
        }

        if (Schema::hasColumn('actions', 'deleted_at')) {
            Schema::table('actions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
