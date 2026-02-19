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
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->string('event', 100)->nullable()->after('status');
            $table->boolean('call_successful')->nullable()->after('event');
            $table->boolean('in_voicemail')->nullable()->after('call_successful');
            $table->string('user_sentiment', 50)->nullable()->after('in_voicemail');
            $table->string('masses_used', 255)->nullable()->after('user_sentiment');
            $table->boolean('busca_automatizar')->nullable()->after('masses_used');
            $table->string('products_mentioned', 255)->nullable()->after('busca_automatizar');
            $table->unsignedInteger('daily_volume_empanadas')->nullable()->after('products_mentioned');
            $table->string('live_attendance_status', 100)->nullable()->after('daily_volume_empanadas');

            $table->index('event');
            $table->index('call_successful');
            $table->index('in_voicemail');
            $table->index('busca_automatizar');
            $table->index('daily_volume_empanadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retell_inbox', function (Blueprint $table) {
            $table->dropIndex(['event']);
            $table->dropIndex(['call_successful']);
            $table->dropIndex(['in_voicemail']);
            $table->dropIndex(['busca_automatizar']);
            $table->dropIndex(['daily_volume_empanadas']);

            $table->dropColumn([
                'event',
                'call_successful',
                'in_voicemail',
                'user_sentiment',
                'masses_used',
                'busca_automatizar',
                'products_mentioned',
                'daily_volume_empanadas',
                'live_attendance_status',
            ]);
        });
    }
};
