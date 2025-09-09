<?php

// database/migrations/2025_09_09_000000_create_retell_inbox_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('retell_inbox', function (Blueprint $t) {
            $t->id();
            $t->string('call_id')->index();         // para idempotencia básica
            $t->string('status')->nullable();
            $t->json('payload');                    // request completo
            $t->timestamp('processed_at')->nullable();
            $t->string('error')->nullable();
            $t->timestamps();

            $t->unique('call_id'); // evita duplicados si Retell reintenta
        });
    }
    public function down() { Schema::dropIfExists('retell_inbox'); }
};
