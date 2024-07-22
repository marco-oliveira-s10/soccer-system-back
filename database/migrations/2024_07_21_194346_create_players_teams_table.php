<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players_teams', function (Blueprint $table) {
            $table->id('id_player_team');
            $table->foreignId('id_team')->constrained('teams', 'id_team')->onDelete('cascade');
            $table->foreignId('id_player')->constrained('players', 'id_player')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players_teams');
    }
}
