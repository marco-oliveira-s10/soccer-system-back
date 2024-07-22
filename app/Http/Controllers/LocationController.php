<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Exception;

class LocationController extends Controller
{
    public function listLocationPagination(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $perPage = $request->query('perPage', 10);

            $locations = Location::where('name_location', '!=', 'Local removido')
            ->orderBy('id_location', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json($locations);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function filterLocationsByName(Request $request)
    {
        try {
            $name = $request->query('name');

            if (empty($name)) {
                throw new Exception('Name was not provided.');
            }

            $locations = Location::where('name_location', 'like', '%' . $name . '%')
            ->where('name_location', '!=', 'Local removido')
            ->get();

            return response()->json($locations);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTotalLocations()
    {
        try {
            $total = Location::where('name_location', '!=', 'Local removido')->count();
            return response()->json(['total' => $total]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request)
    {
        try {
            $data = $request->validate([
                'locationName' => 'required|string',
                'locationLocationName' => 'required|string',
            ]);

            $location = Location::create([
                'name_location' => $data['locationName'],
                'location_location' => $data['locationLocationName'],
                'created_at_location' => now(),
            ]);

            return response()->json($location, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $location = Location::findOrFail($id);
            $location->update(['name_location' => 'Local removido']);
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function findById($id)
    {
        try {
            $location = Location::where('id_location', $id)
            ->where('name_location', '!=', 'Local removido')
            ->firstOrFail();

            return response()->json($location);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'locationName' => 'required|string',
                'locationLocationName' => 'required|string',
            ]);

            $location = Location::findOrFail($id);
            $location->update([
                'name_location' => $data['locationName'],
                'location_location' => $data['locationLocationName'],
            ]);

            return response()->json($location);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}