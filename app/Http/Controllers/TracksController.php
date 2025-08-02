<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class TracksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tracks = User::find(Auth::id())->tracks()->withCount('playlists')->orderBy('added_at')->get();

        return Inertia::render('Tracks/index', [
            'tracks' => $tracks,
        ]);
    }

    public function getTracks(Request $request)
    {
        // dd($request->all()); // Debugging line to check request data
        // Add pagination, filtering, and sorting logic here
        $paginationModel = $request->input('paginationModel', []);
        $filterModel = $request->input('filterModel', []);
        $sortModel = $request->input('sortModel', []);

        // Example logic to fetch tracks based on the request parameters
        $query = User::find(Auth::id())->tracks()->withCount('playlists');

        if (!empty($filterModel)) {
            foreach ($filterModel['quickFilterValues'] as $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('name', 'like', '%' . $value . '%')
                        ->orWhere('artist_name', 'like', '%' . $value . '%')
                        ->orWhere('album_name', 'like', '%' . $value . '%');
                });
            }
        }

        if (!empty($sortModel)) {
            foreach ($sortModel as $sort) {
                $query->orderBy($sort['field'], $sort['sort']);
            }
        }
        $track_count = $query->count();
        // dd($paginationModel); // Debugging line to check pagination model
        // use paginationModel to get the correct page of results
        if (isset($paginationModel['page']) && isset($paginationModel['pageSize'])) {
            $query->skip($paginationModel['page'] * $paginationModel['pageSize'])
                ->take($paginationModel['pageSize']);
        } else {
            // Default to 20 items per page if not specified
            $query->take(20);
        }
        $tracks = $query->get();

        return response()->json([
            'tracks' => $tracks,
            'track_count' => $track_count
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

    /**
     * Display the specified resource.
     */
    public function show(Track $track)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Track $track)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Track $track)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Track $track)
    {
        //
    }
}
