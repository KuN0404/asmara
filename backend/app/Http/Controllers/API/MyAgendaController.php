<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\MyAgenda;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppNotification;

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

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'start_at' => 'required|date',
    //         'until_at' => 'required|date|after:start_at',
    //         'title' => 'required|string',
    //         'description' => 'nullable|string',
    //         'is_show_to_other' => 'required|boolean',
    //         'status' => 'required|in:comming_soon,in_progress,schedule_change,completed,cancelled',
    //     ]);

    //     $validated['created_by'] = $request->user()->id; // FIXED

    //     $agenda = MyAgenda::create($validated);

    // $agenda = MyAgenda::create([
    //     'title' => $request->title,
    //     'start_at' => $request->start_at,
    //     'until_at' => $request->until_at,
    //     'description' => $request->description,
    //     'is_show_to_other' => $request->is_show_to_other ?? false,
    //     'status' => $request->status ?? 'comming_soon',
    //     'created_by' => auth()->id(),
    // ]);

    // // 🚀 SEND WHATSAPP NOTIFICATION TO CREATOR
    // $user = auth()->user();
    // if ($user->whatsapp_number) {
    //     $message = "📝 *AGENDA PRIBADI BARU*\n\n" .
    //               "📌 {$agenda->title}\n" .
    //               "📅 " . $agenda->start_at->format('d M Y') . "\n" .
    //               "🕐 " . $agenda->start_at->format('H:i') . " - " . $agenda->until_at->format('H:i') . "\n" .
    //               ($agenda->description ? "\n📝 {$agenda->description}\n" : "") .
    //               "\n_Agenda pribadi Anda telah dibuat._";

    //     SendWhatsAppNotification::dispatch(
    //         $user->whatsapp_number,
    //         $message,
    //         'my_agenda',
    //         'created',
    //         $agenda->id
    //     );
    // }

    // // return response()->json($agenda, 201);

    //     return response()->json([
    //         'message' => 'Agenda berhasil dibuat',
    //         'agenda' => $agenda,
    //     ], 201);
    // }


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

    // HANYA 1 CREATE
    $agenda = MyAgenda::create([
        'title' => $validated['title'],
        'start_at' => $validated['start_at'],
        'until_at' => $validated['until_at'],
        'description' => $validated['description'] ?? null,
        'is_show_to_other' => $validated['is_show_to_other'],
        'status' => $validated['status'],
        'created_by' => auth()->id(),
    ]);

    // Send WhatsApp - HANYA 1X
    $user = auth()->user();
    if ($user->whatsapp_number) {
        $message = "📝 *AGENDA PRIBADI BARU*\n\n" .
                  "📌 {$agenda->title}\n" .
                  "📅 " . Carbon::parse($agenda->start_at)->format('d M Y') . "\n" .
                  "🕐 " . Carbon::parse($agenda->start_at)->format('H:i') . " - " . Carbon::parse($agenda->until_at)->format('H:i') . "\n" .
                  ($agenda->description ? "\n📝 {$agenda->description}\n" : "") .
                  "\n_Agenda pribadi Anda telah dibuat._";

        SendWhatsAppNotification::dispatch(
            $user->whatsapp_number,
            $message,
            'my_agenda',
            'created',
            $agenda->id
        );
    }

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
