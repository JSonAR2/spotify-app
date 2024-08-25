<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpotifyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/spotify', [SpotifyController::class, 'index'])->name('spotify.spotify');
Route::get('/spotify/user_auth', [SpotifyController::class, 'user_auth']);
// Route::get('/callback', function (Request $request) {
//     dd($request);
//     return $request->all();
// });
Route::get('/callback', [SpotifyController::class, 'callback']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
