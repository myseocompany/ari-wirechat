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
        Schema::table('customer_files', function (Blueprint $table) {
            $table->string('uuid', 255)->nullable()->after('creator_user_id');
            $table->string('filename', 255)->nullable()->after('uuid');
            $table->unsignedBigInteger('size')->nullable()->after('filename');
            $table->string('mime_type', 255)->nullable()->after('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_files', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'filename', 'size', 'mime_type']);
        });
    }
};
