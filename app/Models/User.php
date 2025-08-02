<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'spotify_access_token',
        'token_last_acquired',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tracks()
    {
        return $this->belongsToMany(Track::class, 'tracks_users', 'user_id', 'track_id');
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlists_users', 'user_id', 'playlist_id');
    }

    public function genres()
    {
        // Users have tracks, and tracks have genres.
        // This method returns the genres associated with the user's tracks.
        // It uses a belongsToMany relationship to get genres through tracks.
        return Genres::whereIn('id', function ($query) {
            $query->select('genre_id')
                ->from('genres_tracks')
                ->whereIn('track_id', $this->tracks()->pluck('tracks.id'));
        });
        // )->whereIn('track_id', $this->tracks()->pluck('tracks.id'));
    }
}
