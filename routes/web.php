<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TracksController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaylistsController;
use App\Http\Controllers\GenresController;
use App\Http\Controllers\CommunityController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');



    Route::controller(ProfileController::class)->group(function () {

        Route::get('/profile/edit', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
        Route::get('/profile/{id?}', 'view_profile')->name('profile');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    Route::get('/callback', [SpotifyController::class, 'callback'])->name('spotify.callback');
    Route::controller(SpotifyController::class)->group(function () {
        Route::prefix('spotify')->group(function () {
            Route::get('/', 'index')->name('spotify');
            Route::get('/user_auth', 'user_auth')->name('spotify.user_auth');
            Route::any('/get_client_access_token', 'getClientAccessToken')->name('spotify.get_client_access_token');
            Route::any('/get_user_access_token', 'getUserAccessToken')->name('spotify.get_user_access_token');
            Route::any('/user_auth', 'user_auth')->name('spotify.user_auth');
            Route::any('/get_saved_tracks', 'getSavedTracks')->name('spotify.get_saved_tracks');
            Route::any('/get_playlists', 'getPlaylists')->name('spotify.get_playlists');
            Route::any('/get_playlist_tracks', 'getPlaylistTracks')->name('spotify.get_playlist_tracks');
            Route::any('/updateTracksByAlbum', 'updateTracksByAlbum')->name('spotify.updateTracksByAlbum');
            Route::any('/create_playlist/{playlist_type}', 'createPlaylist')->name('spotify.create_playlist');
            Route::any('/preview_playlist/{playlist_type}', 'previewPlaylist')->name('spotify.get_playlist_tracks');
            Route::any('/create_playlist_by_feature/{playlist_type}', 'createPlaylistByFeature')->name('spotify.create_playlist_by_feature');
            Route::post('/play_track', 'playTrack')->name('spotify.play_track');
            Route::post('/add_track_to_queue', 'addTrackToQueue')->name('spotify.add_track_to_queue');
            Route::post('/play_album', 'playAlbum')->name('spotify.play_album');
            Route::post('/play_playlist', 'playPlaylist')->name('spotify.play_playlist');
        });
    });

    Route::controller(TracksController::class)->group(function () {
        Route::get('/tracks', 'index')->name('tracks');
        Route::post('/tracks/get_tracks', 'getTracks')->name('tracks.get_tracks');
    });

    Route::controller(PlaylistsController::class)->group(function () {
        Route::prefix('playlists')->group(function () {
            Route::get('/', 'index')->name('playlists');
            Route::get('/view_playlist/{id}', 'view_playlist')->name('playlists.view_playlist');
            Route::delete('/delete_playlist', 'delete_playlist')->name('playlists.delete_playlist');
        });
    });

    Route::controller(GenresController::class)->group(function () {
        Route::get('/genres', 'index')->name('genres');
    });


    Route::controller(CommunityController::class)->group(function () {
        Route::prefix('community')->group(function () {
            Route::get('/', 'index')->name('community');
        });
    });


    Route::get('/tracks', [TracksController::class, 'index'])->name('tracks');
});

require __DIR__ . '/auth.php';
