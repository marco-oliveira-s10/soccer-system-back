<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $primaryKey = 'id_team';
    protected $fillable = ['id_event', 'name_team', 'level_team'];

    public function players()
    {
        return $this->belongsToMany(Player::class, 'players_teams', 'id_team', 'id_player');
    }
}
