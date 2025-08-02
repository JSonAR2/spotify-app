<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Track;
use App\Models\Playlist;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // update querys between tracks and users to use belongsToMany relationship
        $user = Auth::user();
        $tracks = $user->tracks;
        $your_tracks = $tracks->count();

        $your_playlists = $user->playlists()->count();
        $saved_tracks = $tracks->where('is_saved', 1)->count();
        $number_of_different_artists = Track::whereIn('id', $tracks->pluck('id'))
            ->distinct('artist_name')
            ->count('artist_name');
        $number_of_different_albums = Track::whereIn('id', $tracks->pluck('id'))
            ->distinct('album_name')
            ->count('album_name');
        $top_5_artists = Track::whereIn('id', $tracks->pluck('id'))
            ->select('artist_name', DB::raw('count(*) as artist_count'))
            ->groupBy('artist_name')
            ->orderBy('artist_count', 'desc')
            ->limit(5)
            ->get();
        $top_5_tracks = Track::whereIn('id', $tracks->pluck('id'))
            ->withCount('playlists')
            ->orderBy('playlists_count', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'saved_tracks' => $saved_tracks,
            'your_tracks' => $your_tracks,
            'your_playlists' => $your_playlists,
            'number_of_different_artists' => $number_of_different_artists,
            'number_of_different_albums' => $number_of_different_albums,
            'top_5_artists' => $top_5_artists,
            'top_5_tracks' => $top_5_tracks,
        ]);
    }
}
