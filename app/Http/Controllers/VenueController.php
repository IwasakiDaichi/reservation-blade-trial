<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    /** Browse venues (students). Deactivated venues are hidden. */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $venues = Venue::visibleToStudents()
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('amenities', 'like', $like);
                });
            })
            ->orderByRaw("CASE status WHEN 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('venues.index', compact('venues', 'search'));
    }

    public function show(Venue $venue)
    {
        abort_unless(in_array($venue->status, VenueStatus::visibleToStudents(), true), 404);

        return view('venues.show', compact('venue'));
    }
}
