<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('customer_tag')) {
            return;
        }

        Schema::create('customer_tag', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->unsignedInteger('tag_id');
            $table->timestamps();

            $table->unique(['customer_id', 'tag_id']);
            $table->index('customer_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_tag');
    }
};
