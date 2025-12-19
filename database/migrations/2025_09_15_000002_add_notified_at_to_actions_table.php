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

        Schema::table('actions', function (Blueprint $table) {
            $table->dateTime('notified_at')->nullable()->after('delivery_date');
            $table->index('notified_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('actions')) {
            return;
        }

        Schema::table('actions', function (Blueprint $table) {
            $table->dropIndex(['notified_at']);
            $table->dropColumn('notified_at');
        });
    }
};
