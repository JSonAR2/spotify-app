<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        // $access_token = $this->getClientAccessToken();
        // $access_token = $this->getUserAccessToken();
        $expired = Carbon::parse(Auth::user()->token_last_acquired)->diffInMinutes(now()) > 60;


        // $response = $this->getSavedTracks($access_token);
        // $response = $this->getPlaylists($access_token);
        // $response = $this->getPlaylistTracks($access_token, $playlist_id);

        // $this->updateTracksByAlbum($access_token);
        // $this->createPlaylist($access_token, 'indie');
        // $this->createPlaylistByFeature($access_token, 'Dancy', 'danceability', 0.8, 300);
        // dump($tracks_array);

        return Inertia::render('Spotify/index', [
            'expired' => $expired,
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

        return redirect('https://accounts.spotify.com/authorize?client_id=fa83b94e477f46619c9c8b18ccf25f79&response_type=code&redirect_uri=https://playlistrix.co.uk/callback&show_dialog=true&scope=' . implode('%20', $scopes));
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
            'redirect_uri' => 'https://playlistrix.co.uk/callback',
        ]);

        $access_token = $response->json()['access_token'];
        $user->update(['spotify_access_token' => $access_token, 'token_last_acquired' => now()]);

        $user->save();
        return redirect()->route('spotify');
    }

    public function getSavedTracks(Request $request)
    {
        $user = Auth::user();
        $access_token = $user->spotify_access_token;
        $tracks_array = [];
        $saved_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/tracks?limit=50')->json();
        // dump($saved_tracks);
        foreach ($saved_tracks['items'] as $track) {
            $entity = Track::firstOrCreate([
                'user_id' => Auth::id(),
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
                'preview_link' => $track['track']['preview_url'],
                'is_saved' => true,
                'added_at' => Carbon::parse($track['added_at']),
            ]);

            $tracks_array[] = $entity;
        }
        file_put_contents('tracks.csv', json_encode($saved_tracks));
        $count = 50;
        while ($count < $saved_tracks['total']) {
            $saved_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/tracks?limit=50&offset=' . $count)->json();

            foreach ($saved_tracks['items'] as $track) {
                $entity = Track::firstOrCreate([
                    'user_id' => Auth::id(),
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
                    'preview_link' => $track['track']['preview_url'],
                    'is_saved' => true,
                    'added_at' => Carbon::parse($track['added_at']),
                ]);

                $tracks_array[] = $entity;
            }
            file_put_contents('tracks2.csv', json_encode($saved_tracks));
            $count += 50;
        }
        $this->updateTracksByAlbum($access_token);
        return redirect()->route('spotify');
    }

    public function getPlaylists(Request $request)
    {
        $user = Auth::user();
        $access_token = $user->spotify_access_token;
        $playlists = Http::withToken($access_token)->retry(3, 10000)->get('https://api.spotify.com/v1/me/playlists?limit=50')->json()['items'];
        $playlists_array = [];
        foreach ($playlists as $playlist) {
            $playlists_array[] = $playlist;
        }
        if (count($playlists_array) == 50) {
            while (count($playlists) == 50) {
                $playlists = Http::withToken($access_token)->retry(3, 10000)->get('https://api.spotify.com/v1/me/playlists?limit=50&offset=' . count($playlists_array))->json()['items'];
                foreach ($playlists as $playlist) {
                    $playlists_array[] = $playlist;
                }
            }
        }
        $playlists_array = array_reverse($playlists_array);
        foreach ($playlists_array as $playlist) {
            if ($playlist['id'] == '0E2ApRdiYataXbFB8YDcrm') {
                dd($playlist);
            } else {
                continue;
            }

            $entity = Playlist::firstOrCreate([
                'user_id' => Auth::id(),
                'spotify_id' => $playlist['id'],
            ]);

            if ($entity->track_count == $playlist['tracks']['total']) {
                continue;
            }

            $entity->update([
                'name' => $playlist['name'],
                'description' => $playlist['description'],
                'track_count' => $playlist['tracks']['total'],
                'collaborative' => $playlist['collaborative'],
            ]);

            $entity->save();

            $this->getPlaylistTracks($access_token, $entity, $entity->spotify_id);
            sleep(5);
        }



        return $playlists;
    }

    public function getPlaylistTracks($access_token, $playlist, $spotify_playlist_id)
    {
        $playlist_tracks = Http::withToken($access_token)->timeout(30)->retry(3, 10000)->get('https://api.spotify.com/v1/playlists/' . $spotify_playlist_id . '/tracks?limit=50')->json()['items'];
        $tracks_array = [];
        foreach ($playlist_tracks as $playlist_track) {
            $tracks_array[] = $playlist_track;
        }
        if (count($playlist_tracks) == 50) {
            while (count($playlist_tracks) == 50) {
                $playlist_tracks = Http::withToken($access_token)->timeout(30)->retry(3, 10000)->get('https://api.spotify.com/v1/playlists/' . $spotify_playlist_id . '/tracks?offset=' . count($tracks_array))->json()['items'];
                foreach ($playlist_tracks as $playlist_track) {
                    $tracks_array[] = $playlist_track;
                }
            }
        }
        $playlist_track_ids = [];
        foreach ($tracks_array  as $track) {
            if ($track['track'] == null) {
                dump($track);
                continue;
            }
            $entity = Track::firstOrCreate([
                'user_id' => Auth::id(),
                'track_id' => $track['track']['id'],
            ]);
            $is_saved = $entity->is_saved;
            if ($entity->is_saved == null || $entity->is_saved == false) {
                $is_saved = false;
            }

            $entity->update([
                'name' => $track['track']['name'],
                'artist_name' => $track['track']['artists'][0]['name'],
                'artist_id' => $track['track']['artists'][0]['id'],
                'album_name' => $track['track']['album']['name'],
                'album_id' => $track['track']['album']['id'],
                'popularity' => $track['track']['popularity'],
                'uri' => $track['track']['uri'],
                'preview_link' => $track['track']['preview_url'],
                'is_saved' => $is_saved,
                'added_at' => Carbon::parse($track['added_at']),
            ]);

            $entity->save();
            $playlist_track_ids[] = $entity->id;
        }
        $playlist->tracks()->sync($playlist_track_ids);
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
                if (!isset($artist_info['artists'])) {
                    dump($artist_info);
                    $id_array = [];
                    continue;
                }
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
                // $audio_features = Http::withToken($access_token)->get('https://api.spotify.com/v1/audio-features?ids=' . implode(',', $track_id_array))->json();
                // // dump($audio_features);
                // if (isset($audio_features['error'])) {
                //     dump($audio_features);
                //     exit;
                // }
                // $audio_features = $audio_features['audio_features'];
                // foreach ($audio_features as $feature) {
                //     if ($feature == null) {
                //         continue;
                //     }
                //     $track = Track::where('track_id', $feature['id'])->first();
                //     $track->update([
                //         'danceability' => $feature['danceability'],
                //         'energy' => $feature['energy'],
                //         'key' => $feature['key'],
                //         'loudness' => $feature['loudness'],
                //         'mode' => $feature['mode'],
                //         'speechiness' => $feature['speechiness'],
                //         'acousticness' => $feature['acousticness'],
                //         'instrumentalness' => $feature['instrumentalness'],
                //         'liveness' => $feature['liveness'],
                //         'valence' => $feature['valence'],
                //         'tempo' => $feature['tempo'],
                //         'time_signature' => $feature['time_signature']
                //     ]);
                //     $track->save();
                // }
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
