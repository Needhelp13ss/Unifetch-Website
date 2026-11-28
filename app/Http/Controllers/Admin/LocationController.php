<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Campus places and their coordinates: the distance fee is worked out from these. */
class LocationController extends Controller
{
    public function index()
    {
        return view('admin.locations', ['locations' => CampusLocation::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        CampusLocation::create($this->data($request));

        return back()->with('success', 'Place added.');
    }

    public function update(Request $request, CampusLocation $location)
    {
        $location->update($this->data($request, $location));

        return back()->with('success', "{$location->name} saved.");
    }

    public function destroy(CampusLocation $location)
    {
        $name = $location->name;
        $location->delete();

        return back()->with('success', "{$name} was removed.");
    }

    private function data(Request $request, ?CampusLocation $ignore = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('campus_locations', 'name')->ignore($ignore?->id)],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], ['name.unique' => 'That place already exists.']);
    }
}
