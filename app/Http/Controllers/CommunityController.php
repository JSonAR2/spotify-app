<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        // Other users 
        $users = User::where('id', '!=', auth()->id())
            ->withCount(['tracks as track_count', 'tracks as common_track_count' => function ($query) {
                $query->whereIn('tracks.id', auth()->user()->tracks->pluck('id'));
            }])
            ->with(['tracks.genre' => function ($query) {
                $query->select('genres.id', 'genres.name');
            }])
            ->orderBy('track_count', 'desc')
            ->get();

        // Get top genres for each user similar to DashboardController
        foreach ($users as $user) {
            $user->top_genres = $user->genres()
                ->withCount('tracks as track_count')
                ->orderBy('track_count', 'desc')
                ->limit(3)
                ->get();
        }

        // Fetch community posts or data

        return Inertia::render('Community/index', [
            'users' => $users,
        ]);
    }
}
