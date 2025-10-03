<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $participants = Participant::paginate($request->per_page ?? 15);
        return response()->json($participants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'organization' => 'nullable|string',
        ]);

        $participant = Participant::create($validated);

        return response()->json([
            'message' => 'Partisipan berhasil dibuat',
            'participant' => $participant,
        ], 201);
    }

    public function show($id)
    {
        $participant = Participant::findOrFail($id);
        return response()->json($participant);
    }

    public function update(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'organization' => 'nullable|string',
        ]);

        $participant->update($validated);

        return response()->json([
            'message' => 'Partisipan berhasil diperbarui',
            'participant' => $participant,
        ]);
    }

    public function destroy($id)
    {
        $participant = Participant::findOrFail($id);
        $participant->delete();

        return response()->json([
            'message' => 'Partisipan berhasil dihapus',
        ]);
    }
}
