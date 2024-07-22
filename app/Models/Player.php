<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $table = 'players';
    protected $primaryKey = 'id_player';

    protected $fillable = [
        'name_player',
        'level_player',
        'position_player',
        'age_player',
    ];

    public $timestamps = true;
}
