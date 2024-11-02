<?php

namespace App\Http\Controllers;

use App\Models\DeliveryPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryPositionController extends Controller
{
    /**
     * Store a newly created delivery position in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Enregistrement de la position
        $position = DeliveryPosition::create([
            'course_id' => $validated['course_id'],
            'duration' => 0,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json(['message' => 'Position enregistrée', 'data' => $position], 201);
    }

    //Recup des positions
    public function getPositionsByCourseId($courseId)
    {
        // Récupérer toutes les positions de livraison pour une course donnée
        $positions = DeliveryPosition::where('course_id', $courseId)->get();

        // Vérifier si des positions existent
        if ($positions->isEmpty()) {
            return response()->json(['message' => 'No delivery positions found for this course.'], 404);
        }

        return response()->json($positions);
    }

    /**
     * Display the specified delivery position.
     */
    public function show($courseId)
    {
        $positions = DeliveryPosition::where('course_id', $courseId)->get();

        return response()->json($positions);
    }

    /**
     * Remove the specified delivery position from storage.
     */
    public function destroy($id)
    {
        $position = DeliveryPosition::findOrFail($id);
        $position->delete();

        return response()->json(null, 204);
    }

    // récupérer le premier point du livreur
    public function getFirstPosition($courseId)
    {
        // Récupérer la première position de la course
        $firstPosition = DB::table('delivery_positions')
            ->where('course_id', $courseId)
            ->orderBy('created_at', 'asc')
            ->first();

        return response()->json($firstPosition);
    }
}
