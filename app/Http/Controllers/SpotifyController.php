<?php

namespace App\Http\Controllers;

use App\Jobs\SpotifyGetSavedTracks;
use App\Jobs\SpotifyGetPlaylists;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Track;
use App\Models\Playlist;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\SpotifyService;

class SpotifyController extends Controller
{
    protected $spotifyService;
    protected $expired;

    // initialise the controller
    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
        // Check user access token and refresh if expired
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->spotify_access_token == null) {
                $this->expired = true;
            } else {
                $this->expired = Carbon::parse($user->token_last_acquired)->diffInMinutes(now()) > 60;
                if ($this->expired) {
                    $this->spotifyService->refreshToken();
                    $this->expired = false;
                }
            }
        }
    }

    public function user_auth()
    {
        $scopes = [
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'playlist-read-collaborative',
            'playlist-modify-public',
            'playlist-modify-private',
            'user-library-read',
            'user-library-modify',
            'user-read-playback-state',
            'user-modify-playback-state',
            'user-read-currently-playing',
            'user-read-recently-played',
            'user-top-read',
            'user-follow-read',
            'user-follow-modify',
        ];

        return redirect('https://accounts.spotify.com/authorize?client_id=fa83b94e477f46619c9c8b18ccf25f79&response_type=code&redirect_uri=https://playlistrix.co.uk/callback&show_dialog=true&scope=' . implode('%20', $scopes));
    }

    public function callback(Request $request)
    {
        $id = Auth::id();
        $user = User::find($id);
        $code = $request->all()['code'];
        $response = Http::withBasicAuth('fa83b94e477f46619c9c8b18ccf25f79', '074eda52578c42a387c7393731452505')->asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'https://playlistrix.co.uk/callback',
        ]);
        $access_token = $response->json()['access_token'];
        $user->update(['spotify_access_token' => $access_token, 'token_last_acquired' => now(), 'spotify_refresh_token' => $response->json()['refresh_token']]);

        $user->save();
        return redirect()->route('spotify');
    }

    public function index()
    {
        // $access_token = $this->getUserAccessToken();
        // $expired = Carbon::parse(Auth::user()->token_last_acquired)->diffInMinutes(now()) > 60;


        // $response = $this->getSavedTracks($access_token);
        // $response = $this->getPlaylists($access_token);
        // $response = $this->getPlaylistTracks($access_token, $playlist_id);

        // $this->updateTracksByArtist($access_token);
        // $this->createPlaylist($access_token, 'indie');
        // $this->createPlaylistByFeature($access_token, 'Dancy', 'danceability', 0.8, 300);

        return Inertia::render('Spotify/index', [
            'expired' => $this->expired,
        ]);
    }

    public function getUserAccessToken()
    {
        $user = Auth::user();
        $access_token = $user->spotify_access_token;
        return $access_token;
    }

    public function getSavedTracks(Request $request)
    {
        SpotifyGetSavedTracks::dispatch(Auth::user());
        return response()->json([
            'message' => 'Fetching saved tracks in the background.',
        ], Response::HTTP_ACCEPTED);
    }

    public function getPlaylists(Request $request)
    {

        SpotifyGetPlaylists::dispatch(Auth::user());
        return response()->json([
            'message' => 'Fetching playlists in the background.',
        ], Response::HTTP_ACCEPTED);
    }

    public function playTrack(Request $request)
    {
        $service = new SpotifyService();
        $service->playTrack(Auth::user(), $request->track_id);
        return response()->json(['message' => 'Track set to play'], Response::HTTP_OK);
    }

    public function addTrackToQueue(Request $request)
    {
        $service = new SpotifyService();
        $service->addTrackToQueue(Auth::user(), $request->track_id);
        return response()->json(['message' => 'Track added to queue'], Response::HTTP_OK);
    }

    public function playAlbum(Request $request)
    {
        $service = new SpotifyService();
        $service->playAlbum(Auth::user(), $request->album_id);
        return response()->json(['message' => 'Album set to play'], Response::HTTP_OK);
    }

    public function playPlaylist(Request $request)
    {
        $service = new SpotifyService();
        $service->playPlaylist(Auth::user(), $request->id);
        return response()->json(['message' => 'Playlist set to play'], Response::HTTP_OK);
    }



    public function createPlaylist($playlist_type)
    {
        $access_token = $this->getUserAccessToken();
        $pop_tracks = Track::where('genres', 'like', '%' . $playlist_type . '%')->get();
        // dd($pop_tracks);
        $playlist = Http::withToken($access_token)->post('https://api.spotify.com/v1/me/playlists', [
            'name' => ucfirst($playlist_type) . ' Tracks',
            'description' => 'A playlist of the ' . $playlist_type . ' tracks in your saved songs',
            'public' => false,
        ])->json();

        $playlist_id = $playlist['id'];
        $track_uris = [];

        foreach ($pop_tracks as $track) {
            $track_uris[] = $track->uri;
            if (count($track_uris) == 100) {
                Http::withToken($access_token)->post('https://api.spotify.com/v1/playlists/' . $playlist_id . '/tracks', [
                    'uris' => $track_uris,
                ]);
                $track_uris = [];
            }
        }

        Http::withToken($access_token)->post('https://api.spotify.com/v1/playlists/' . $playlist_id . '/tracks', [
            'uris' => $track_uris,
        ]);
    }

    public function previewPlaylist($playlist_type)
    {
        $access_token = $this->getUserAccessToken();
        $pop_tracks = Track::where('genres', 'like', '%' . $playlist_type . '%')->get();

        return $pop_tracks;
    }

    public function createPlaylistByFeature($access_token, $name, $feature, $min, $max)
    {
        $tracks = Track::where($feature, '>=', $min)->where($feature, '<=', $max)->get();
        $playlist = Http::withToken($access_token)->post('https://api.spotify.com/v1/me/playlists', [
            'name' => ucfirst($name) . ' Tracks from Liked Songs',
            'description' => 'A playlist of the ' . $name . ' tracks in your saved songs',
            'public' => false,
        ])->json();

        $playlist_id = $playlist['id'];
        $track_uris = [];
        foreach ($tracks as $track) {
            $track_uris[] = $track->uri;
            if (count($track_uris) == 100) {
                $response = Http::withToken($access_token)->post('https://api.spotify.com/v1/playlists/' . $playlist_id . '/tracks', [
                    'uris' => $track_uris,
                ]);
                $track_uris = [];
            }
        }
        $response = Http::withToken($access_token)->post('https://api.spotify.com/v1/playlists/' . $playlist_id . '/tracks', [
            'uris' => $track_uris,
        ]);
    }
}
