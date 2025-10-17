<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MyAgenda;
use Illuminate\Http\Request;

class MyAgendaController extends Controller
{
public function index(Request $request)
{
    $query = MyAgenda::with('creator')
        ->withTrashed()
        ->where('created_by', $request->user()->id);

    // Tambah filter tanggal
    if ($request->start_date) {
        $query->whereDate('start_at', '>=', $request->start_date);
    }

    if ($request->end_date) {
        $query->whereDate('start_at', '<=', $request->end_date);
    }

    if ($request->status) {
        $query->where('status', $request->status);
    }

    $agendas = $query->orderBy('start_at', 'desc')
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

        $validated['created_by'] = $request->user()->id; // FIXED

        $agenda = MyAgenda::create($validated);

        return response()->json([
            'message' => 'Agenda berhasil dibuat',
            'agenda' => $agenda,
        ], 201);
    }

public function show($id)
{
    $agenda = MyAgenda::with('creator')
        ->withTrashed() // TAMBAHKAN INI
        ->where('created_by', auth()->id())
        ->findOrFail($id);

    return response()->json($agenda);
}

public function update(Request $request, $id)
{
    $agenda = MyAgenda::withTrashed() // TAMBAHKAN INI
        ->where('created_by', $request->user()->id)
        ->findOrFail($id);

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
        $agenda = MyAgenda::where('created_by', auth()->id())->findOrFail($id); // FIXED
        $agenda->delete();

        return response()->json([
            'message' => 'Agenda berhasil dihapus',
        ]);
    }

public function publicAgendas(Request $request)
{
    $query = MyAgenda::with('creator')
        ->withTrashed()
        ->where('is_show_to_other', true)
        ->where('created_by', '!=', $request->user()->id);

    // Tambah filter tanggal
    if ($request->start_date) {
        $query->whereDate('start_at', '>=', $request->start_date);
    }

    if ($request->end_date) {
        $query->whereDate('start_at', '<=', $request->end_date);
    }

    $agendas = $query->orderBy('start_at', 'desc')
        ->paginate($request->per_page ?? 15);

    return response()->json($agendas);
}

}
