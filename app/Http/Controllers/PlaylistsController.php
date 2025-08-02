<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class PlaylistsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $playlists = $user->playlists()->orderBy('created_at')->get();

        return Inertia::render('Playlists/index', [
            'playlists' => $playlists,
        ]);
    }

    public function view_playlist(Request $request)
    {
        $playlist = Playlist::find($request->id);
        $tracks = $playlist->tracks;

        return Inertia::render('Playlists/view', [
            'playlist' => $playlist,
            'tracks' => $tracks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function delete_playlist(Request $request)
    {
        $playlist = Playlist::find($request->id);
        $spotify_id = $playlist->spotify_id;


        $response = Http::withToken(Auth::user()->spotify_access_token)->delete('https://api.spotify.com/v1/playlists/' . $spotify_id . '/followers');

        if ($response->successful()) {
            $playlist->tracks()->detach();
            $unlinked_tracks = Track::where('user_id', Auth::id())->whereDoesntHave('playlists')->where('is_saved', false)->get();
            foreach ($unlinked_tracks as $track) {
                $track->delete();
            }
            $playlist->delete();
            return response()->json(['message' => 'Playlist deleted']);
        } else {
            return response()->json(['message' => 'Failed to delete playlist on Spotify'], 500);
        }
    }
}
