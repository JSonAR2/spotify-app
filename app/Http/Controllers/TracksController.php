<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TracksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->input('q');
        $tracks = Track::where('user_id', Auth::id())->when($request->has('sort_field'), function ($query) use ($request) {
            $sortField = $request->input('sort_field');
            $sortDir = $request->input('sort_dir', 'asc');
            $query->orderBy($sortField, $sortDir);
        })->paginate(50);
        if (
            $request->header('hx-request')
            && $request->header('hx-target') == 'table-container'
        ) {
            return view('tracks.partials.table', compact('tracks'));
        }
        return view('tracks.index', compact('tracks'));
    }

    public function get_tracks()
    {
        $tracks = Track::where('user_id', Auth::id())->get();
        return response()->json($tracks);
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
