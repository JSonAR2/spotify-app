<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\TracksController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/spotify', [SpotifyController::class, 'index'])->name('spotify');
Route::get('/spotify/user_auth', [SpotifyController::class, 'user_auth']);
Route::get('/spotify/get_saved_tracks/{access_token}', [SpotifyController::class, 'getSavedTracks']);
Route::get('/callback', [SpotifyController::class, 'callback']);

Route::get('/tracks', [TracksController::class, 'index'])->name('tracks');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
