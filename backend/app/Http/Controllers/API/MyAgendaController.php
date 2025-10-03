<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MyAgenda;
use Illuminate\Http\Request;

class MyAgendaController extends Controller
{
    public function index(Request $request)
    {
        $agendas = MyAgenda::with('creator')
            ->where('ceated_by', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('start_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($agendas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_at' => 'required|date',
            'until_at' => 'required|date|after:start_at',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'is_show_to_other' => 'required|boolean',
            'status' => 'required|in:comming_soon,in_progress,schedule_change,completed,cancelled',
        ]);

        $validated['ceated_by'] = $request->user()->id;

        $agenda = MyAgenda::create($validated);

        return response()->json([
            'message' => 'Agenda berhasil dibuat',
            'agenda' => $agenda,
        ], 201);
    }

    public function show($id)
    {
        $agenda = MyAgenda::with('creator')
            ->where('ceated_by', auth()->id())
            ->findOrFail($id);

        return response()->json($agenda);
    }

    public function update(Request $request, $id)
    {
        $agenda = MyAgenda::where('ceated_by', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'start_at' => 'sometimes|date',
            'until_at' => 'sometimes|date|after:start_at',
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'is_show_to_other' => 'sometimes|boolean',
            'status' => 'sometimes|in:comming_soon,in_progress,schedule_change,completed,cancelled',
        ]);

        $agenda->update($validated);

        return response()->json([
            'message' => 'Agenda berhasil diperbarui',
            'agenda' => $agenda,
        ]);
    }

    public function destroy($id)
    {
        $agenda = MyAgenda::where('ceated_by', auth()->id())->findOrFail($id);
        $agenda->delete();

        return response()->json([
            'message' => 'Agenda berhasil dihapus',
        ]);
    }

    // Get other users' public agendas
    public function publicAgendas(Request $request)
    {
        $agendas = MyAgenda::with('creator')
            ->where('is_show_to_other', true)
            ->where('ceated_by', '!=', $request->user()->id)
            ->orderBy('start_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($agendas);
    }
}
