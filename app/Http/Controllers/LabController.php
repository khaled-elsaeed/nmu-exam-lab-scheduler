<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LabController extends Controller
{
    public function index()
    {
        try {
            $labs = Lab::all();
            $totalLabs = $labs->count();
            $totalCapacity = $labs->sum('capacity');

            return view('admin.labs.index', compact('labs', 'totalLabs', 'totalCapacity'));
        } catch (\Exception $e) {
            Log::error('Error retrieving labs: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json(['error' => 'Failed to retrieve labs. Please try again later.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'building' => 'required|string|max:255',
                'floor' => 'required|string|max:255',
                'number' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
            ]);

            $lab = Lab::create([
                'building' => $request->building,
                'floor' => $request->floor,
                'number' => $request->number,
                'capacity' => $request->capacity,
            ]);

            return response()->json($lab, 201);
        } catch (\Exception $e) {
            Log::error('Error creating lab: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json(['error' => 'Failed to create lab. Please try again later.'], 500);
        }
    }

    public function update(Request $request, Lab $lab)
    {
        try {
            $validatedData = $request->validate([
                'building' => 'required|string|max:255',
                'floor' => 'required|string|max:255',
                'number' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
            ]);

            $lab->update([
                'building' => $validatedData['building'],
                'floor' => $validatedData['floor'],
                'number' => $validatedData['number'],
                'capacity' => $validatedData['capacity'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lab details updated successfully.',
                'lab' => $lab
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating lab details: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e,
                'lab_id' => $lab->id
            ]);

            return response()->json([
                'error' => 'Failed to update lab details. Please try again later.',
                'details' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $lab = Lab::findOrFail($id);
            $lab->delete();

            return response()->json(['message' => 'Lab deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting lab with ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'Failed to delete lab. Please try again later.'], 500);
        }
    }
}
