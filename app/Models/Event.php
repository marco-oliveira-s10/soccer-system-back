<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $primaryKey = 'id_event';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'name_event',
        'id_location',
        'date_event',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'id_location', 'id_location');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'id_event', 'id_event');
    }

    public function scopeWithDetails($query, $id)
    {
        return $query->with(['teams.players'])->where('id_event', $id);
    }
}
