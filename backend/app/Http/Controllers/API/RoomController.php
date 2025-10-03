<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::when($request->is_available, function ($q) use ($request) {
            $q->where('is_available', $request->is_available);
        })->paginate($request->per_page ?? 15);

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'nullable|integer',
            'is_available' => 'boolean',
        ]);

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruang berhasil dibuat',
            'room' => $room,
        ], 201);
    }

    public function show($id)
    {
        $room = Room::with('officeAgendas')->findOrFail($id);
        return response()->json($room);
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'location' => 'sometimes|string',
            'capacity' => 'nullable|integer',
            'is_available' => 'boolean',
        ]);

        $room->update($validated);

        return response()->json([
            'message' => 'Ruang berhasil diperbarui',
            'room' => $room,
        ]);
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return response()->json([
            'message' => 'Ruang berhasil dihapus',
        ]);
    }
}
