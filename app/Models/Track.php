<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'track_id',
        'name',
        'artist_name',
        'artist_id',
        'album_name',
        'album_id',
        'popularity',
        'genres',
        'uri',
        'acousticness',
        'danceability',
        'energy',
        'instrumentalness',
        'liveness',
        'loudness',
        'mode',
        'speechiness',
        'tempo',
        'time_signature',
        'valence',
        'preview_link',
        'is_saved',
        'added_at',
    ];


    public function playlists()
    {
        return $this->belongsToMany(Playlist::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tracks_users', 'track_id', 'user_id');
    }
}
