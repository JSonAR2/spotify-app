<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spotify_id',
        'name',
        'description',
        'track_count',
        'collaborative',
    ];

    public function tracks()
    {
        return $this->belongsToMany(Track::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'playlists_users', 'playlist_id', 'user_id');
    }
}
