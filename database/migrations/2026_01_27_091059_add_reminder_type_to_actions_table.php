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
        Schema::table('actions', function (Blueprint $table) {
            $table->string('reminder_type')->nullable()->index();
            $table->index(
                ['type_id', 'object_id', 'reminder_type', 'deleted_at'],
                'actions_type_object_reminder_deleted_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->dropIndex('actions_type_object_reminder_deleted_idx');
            $table->dropIndex(['reminder_type']);
            $table->dropColumn('reminder_type');
        });
    }
};
