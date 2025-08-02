<?php

namespace App\Services;

use App\Models\Genres;
use Illuminate\Support\Facades\Http;
use App\Models\Track;
use App\Models\User;
use App\Models\Playlist;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * SpotifyService class to handle Spotify-related operations.
 */

class SpotifyService
{
    public function refreshToken()
    {
        $user = Auth::user();
        $response = Http::asForm()->withBasicAuth('fa83b94e477f46619c9c8b18ccf25f79', '074eda52578c42a387c7393731452505')->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->spotify_refresh_token,
        ]);
        $response = $response->json();
        $user->update([
            'spotify_access_token' => $response['access_token'],
            'token_last_acquired' => now(),
        ]);
        $user->save();
    }

    public function getSavedTracks($user = null)
    {
        if (Auth::check() && !$user) {
            $user = Auth::user();
        }
        $access_token = $user->spotify_access_token;
        $tracks_array = [];
        $saved_tracks = Http::withToken($access_token)->get('https://api.spotify.com/v1/me/tracks?limit=50')->json();
        $track_ids_array = [];
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
                'preview_link' => $track['track']['preview_url'],
                'is_saved' => true,
                'added_at' => Carbon::parse($track['added_at']),
            ]);
            $entity->users()->syncWithoutDetaching($user->id);

            $tracks_array[] = $entity;
            $track_ids_array[] = $entity->id;
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
                    'preview_link' => $track['track']['preview_url'],
                    'is_saved' => true,
                    'added_at' => Carbon::parse($track['added_at']),
                ]);
                $entity->users()->syncWithoutDetaching($user->id);
                $tracks_array[] = $entity;
                $track_ids_array[] = $entity->id;
            }
            $count += 50;
        }
        $this->updateTracksByArtist($access_token, $track_ids_array);
        return redirect()->route('spotify');
    }

    public function updateTracksByArtist($access_token, $tracks = null)
    {
        if ($tracks == null) {
            $tracks = Track::all();
        } elseif (is_array($tracks)) {
            $tracks = Track::whereIn('id', $tracks)->get();
        }
        $count = 1;
        $id_array = [];
        $track_id_array = [];
        foreach ($tracks as $track) {
            $id_array[] = $track->artist_id;
            $track_id_array[] = $track->track_id;
            if ($count == 20) {
                $artist_info = Http::withToken($access_token)->get('https://api.spotify.com/v1/artists?ids=' . implode(',', $id_array))->json();
                if (!isset($artist_info['artists'])) {
                    dump($track);
                    dump($artist_info);
                    $id_array = [];
                    continue;
                }
                foreach ($artist_info['artists'] as $artist) {

                    $tracks = Track::where('artist_id', $artist['id']);

                    if ($tracks->count() == 1) {
                        $track = $tracks->first();

                        $track->update(['genres' => implode(',', $artist['genres'])]);
                        foreach ($artist['genres'] as $genre) {
                            $saved_genre = Genres::firstOrCreate(['name' => ucwords($genre)]);
                            $track->genres()->sync($saved_genre->id);
                        }
                        $track->save();
                    } else {
                        $tracks = $tracks->get();
                        foreach ($tracks as $track) {
                            $track->update(['genres' => implode(',', $artist['genres'])]);
                            $track->save();

                            foreach ($artist['genres'] as $genre) {
                                $saved_genre = Genres::firstOrCreate(['name' => ucwords($genre)]);
                                $track->genres()->sync($saved_genre->id);
                            }
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

    public function getPlaylists($user = null)
    {
        if (Auth::check() && !$user) {
            $user = Auth::user();
        }
        $access_token = $user->spotify_access_token;
        $playlists = Http::withToken($access_token)->retry(3, 10000)->get('https://api.spotify.com/v1/me/playlists?limit=50')->json()['items'];
        $playlists_array = [];
        // dd($playlists);

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

            $entity = Playlist::firstOrCreate([
                'spotify_id' => $playlist['id'],
            ]);
            $entity->users()->syncWithoutDetaching($user->id);

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
            $this->getPlaylistTracks($access_token, $entity, $entity->spotify_id, $user);
            sleep(5);
        }
        return $playlists;
    }

    public function getPlaylistTracks($access_token, $playlist, $spotify_playlist_id, $user = null)
    {
        if (Auth::check() && !$user) {
            $user = Auth::user();
        }
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
            $entity->users()->syncWithoutDetaching($user->id);

            $entity->save();
            $playlist_track_ids[] = $entity->id;
        }
        $playlist->tracks()->sync($playlist_track_ids);
        $this->updateTracksByArtist($access_token, $playlist_track_ids);
        return $playlist_tracks;
    }

    public function playTrack($user, $track_id)
    {
        $access_token = $user->spotify_access_token;
        $track = Track::find($track_id);
        $response = Http::withToken($access_token)->put('https://api.spotify.com/v1/me/player/play', [
            'uris' => [$track->uri],
        ]);
    }

    public function addTrackToQueue($user, $track_id)
    {
        $access_token = $user->spotify_access_token;
        $track = Track::find($track_id);
        $response = Http::withToken($access_token)->post('https://api.spotify.com/v1/me/player/queue?uri=' . $track->uri);
        return $response->successful();
    }

    public function playPlaylist($user, $playlist_id)
    {
        $access_token = $user->spotify_access_token;
        $playlist = Playlist::find($playlist_id);
        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }
        $response = Http::withToken($access_token)->put('https://api.spotify.com/v1/me/player/play', [
            'context_uri' => 'spotify:playlist:' . $playlist->spotify_id,
        ]);
        return $response->successful();
    }

    public function playAlbum($user, $album_id)
    {
        $access_token = $user->spotify_access_token;
        $response = Http::withToken($access_token)->put('https://api.spotify.com/v1/me/player/play', [
            'context_uri' => 'spotify:album:' . $album_id,
        ]);
        return $response->successful();
    }
}
