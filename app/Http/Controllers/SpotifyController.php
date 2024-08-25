<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class SpotifyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
        //     'grant_type' => 'client_credentials',
        //     'client_id' => 'fa83b94e477f46619c9c8b18ccf25f79',
        //     'client_secret' => '074eda52578c42a387c7393731452505'
        // ]);
        // $response = $response->json();
        // $access_token = $response['access_token'];
        $user = Auth::user();
        $access_token = $user->spotify_access_token;
        $playlists = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/playlists')->json()['items'];
        dump($playlists[0]);
        $playlist_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/playlists/' . $playlists[0]['id'] . '/tracks')->json()['items'];
        dump($playlist_tracks);
        exit;
        return view('spotify.spotify', [
            // 'user' => $request->user(),
        ]);
    }

    public function user_auth()
    {
        return redirect('https://accounts.spotify.com/authorize?client_id=fa83b94e477f46619c9c8b18ccf25f79&response_type=code&redirect_uri=http://localhost:8000/callback');
        // $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
        //     'client_id' => 'fa83b94e477f46619c9c8b18ccf25f79',
        //     'response_type' => 'code',
        //     'redirect_uri' => 'http://localhost:8000/callback',
        // ]);
    }

    public function callback(Request $request)
    {
        $id = Auth::id();
        $user = User::find($id);
        $code = $request->all()['code'];
        $bearer = base64_encode('fa83b94e477f46619c9c8b18ccf25f79:074eda52578c42a387c7393731452505');
        $response = Http::withBasicAuth('fa83b94e477f46619c9c8b18ccf25f79', '074eda52578c42a387c7393731452505')->asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'http://localhost:8000/callback',
        ]);

        $access_token = $response->json()['access_token'];
        $user->update(['spotify_access_token' => $access_token, 'token_last_acquired' => now()]);

        $user->save();
        return redirect()->route('spotify.spotify');
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

    /**
     * Display the specified resource.
     */
    // public function show(Spotify $Spotify)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Spotify $Spotify)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Spotify $Spotify)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Spotify $Spotify)
    // {
    //     //
    // }
}
