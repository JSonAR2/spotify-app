<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;


class SpotifyController extends Controller
{
    public function getClientAccessToken()
    {
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'fa83b94e477f46619c9c8b18ccf25f79',
            'client_secret' => '074eda52578c42a387c7393731452505'
        ]);
        $response = $response->json();
        $access_token = $response['access_token'];
        return $access_token;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // $access_token = $this->getClientAccessToken();
        $access_token = $this->getUserAccessToken();


        // $response = $this->getSavedTracks($access_token);
        // $response = $this->getPlaylists($access_token);
        // $response = $this->getPlaylistTracks($access_token, $playlist_id);

        // $this->updateTracksByAlbum($access_token);
        // $this->createPlaylist($access_token, 'indie');
        // $this->createPlaylistByFeature($access_token, 'Dancy', 'danceability', 0.8, 300);
        // dump($tracks_array);

        return view('spotify.index', [
            // 'user' => $request->user(),
        ]);
    }

    public function getUserAccessToken()
    {
        $user = Auth::user();
        $access_token = $user->spotify_access_token;
        return $access_token;
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

        return redirect('https://accounts.spotify.com/authorize?client_id=fa83b94e477f46619c9c8b18ccf25f79&response_type=code&redirect_uri=http://localhost:8000/callback&show_dialog=true&scope=' . implode('%20', $scopes));
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
        return redirect()->route('spotify');
    }

    public function getSavedTracks($access_token)
    {
        $tracks_array = [];
        $saved_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/tracks?limit=50')->json();
        foreach ($saved_tracks['items'] as $track) {
            $entity = Track::firstOrCreate([
                'track_id' => $track['track']['id'],
            ]);

            $entity->update([
                'name' => $track['track']['name'],
                'artist_name' => $track['track']['artists'][0]['name'],
                'artist_id' => $track['track']['artists'][0]['id'],
                'album_name' => $track['track']['album']['name'],
                'album_id' => $track['track']['album']['id'],
                'popularity' => $track['track']['popularity'],
                'uri' => $track['track']['uri'],
            ]);

            $tracks_array[] = $entity;
        }
        $count = 50;
        while ($count < $saved_tracks['total']) {
            $saved_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/tracks?limit=50&offset=' . $count)->json();
            foreach ($saved_tracks['items'] as $track) {
                $entity = Track::firstOrCreate([
                    'track_id' => $track['track']['id'],
                ]);

                $entity->update([
                    'name' => $track['track']['name'],
                    'artist_name' => $track['track']['artists'][0]['name'],
                    'artist_id' => $track['track']['artists'][0]['id'],
                    'album_name' => $track['track']['album']['name'],
                    'album_id' => $track['track']['album']['id'],
                    'popularity' => $track['track']['popularity'],
                    'uri' => $track['track']['uri'],
                ]);

                $tracks_array[] = $entity;
            }
            $count += 50;
        }
        $this->updateTracksByAlbum($access_token);
        return redirect()->route('spotify');
    }

    public function getPlaylists($access_token)
    {
        $playlists = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/playlists')->json()['items'];
        return $playlists;
    }

    public function getPlaylistTracks($access_token, $playlist_id)
    {
        $playlist_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/playlists/' . $playlist_id . '/tracks')->json();
        return $playlist_tracks;
    }

    public function updateTracksByAlbum($access_token)
    {
        $tracks = Track::all();
        $count = 1;
        $id_array = [];
        $track_id_array = [];
        foreach ($tracks as $track) {
            $id_array[] = $track->artist_id;
            $track_id_array[] = $track->track_id;
            if ($count == 20) {
                $artist_info = Http::withToken($access_token)->get('https://api.spotify.com/v1/artists?ids=' . implode(',', $id_array))->json();
                foreach ($artist_info['artists'] as $artist) {
                    $tracks = Track::where('artist_id', $artist['id']);
                    // dump($tracks->count());
                    // dump($tracks);
                    // dump($artist);
                    // exit;
                    if ($tracks->count() == 1) {
                        $track = $tracks->first();

                        $track->update(['genres' => implode(',', $artist['genres'])]);

                        $track->save();
                    } else {
                        $tracks = $tracks->get();
                        foreach ($tracks as $track) {
                            // dd($track);
                            $track->update(['genres' => implode(',', $artist['genres'])]);
                            $track->save();
                        }
                    }
                }
                $audio_features = Http::withToken($access_token)->get('https://api.spotify.com/v1/audio-features?ids=' . implode(',', $track_id_array))->json();
                // dump($audio_features);
                if (isset($audio_features['error'])) {
                    dump($audio_features);
                    exit;
                }
                $audio_features = $audio_features['audio_features'];
                foreach ($audio_features as $feature) {
                    if ($feature == null) {
                        continue;
                    }
                    $track = Track::where('track_id', $feature['id'])->first();
                    $track->update([
                        'danceability' => $feature['danceability'],
                        'energy' => $feature['energy'],
                        'key' => $feature['key'],
                        'loudness' => $feature['loudness'],
                        'mode' => $feature['mode'],
                        'speechiness' => $feature['speechiness'],
                        'acousticness' => $feature['acousticness'],
                        'instrumentalness' => $feature['instrumentalness'],
                        'liveness' => $feature['liveness'],
                        'valence' => $feature['valence'],
                        'tempo' => $feature['tempo'],
                        'time_signature' => $feature['time_signature']
                    ]);
                    $track->save();
                }
                $count = 0;
                $track_id_array = [];
                $id_array = [];
            }
            $count++;
        }
        // dd($tracks);
        // foreach ($tracks as $track) {
        //     $album_id = $track->album_id;
        //     $album_info = Http::withToken($access_token)->get('https://api.spotify.com/v1/albums/' . $album_id)->json();
        //     $track->update(['genres' => implode(',', $album_info['genres'])]);
        //     $track->save();
        // }
    }

    public function createPlaylist($access_token, $playlist_type)
    {
        $pop_tracks = Track::where('genres', 'like', '%' . $playlist_type . '%')->get();
        $playlist = Http::withToken($access_token)->post('https://api.spotify.com/v1/me/playlists', [
            'name' => ucfirst($playlist_type) . ' Tracks from Liked Songs',
            'description' => 'A playlist of the ' . $playlist_type . ' tracks in your saved songs',
            'public' => false,
        ])->json();

        $playlist_id = $playlist['id'];
        $track_uris = [];
        foreach ($pop_tracks as $track) {
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
