<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genres extends Model
{
    //
    protected $fillable = [
        'name',
    ];

    public function tracks()
    {
        return $this->belongsToMany(Track::class, 'genres_tracks', 'genre_id', 'track_id');
    }
}
