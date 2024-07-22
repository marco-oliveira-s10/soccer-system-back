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
        Schema::create('players', function (Blueprint $table) {
            $table->id('id_player');
            $table->string('name_player');
            $table->integer('level_player');
            $table->string('position_player');
            $table->integer('age_player');
            $table->timestamps();
            $table->index('name_player'); // Ensure that the 'name_player' column is not an empty field
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
};
