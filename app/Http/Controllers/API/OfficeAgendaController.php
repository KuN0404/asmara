<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OfficeAgenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfficeAgendaController extends Controller
{
    public function index(Request $request)
    {
        $agendas = OfficeAgenda::with([
            'room',
            'participants',
            'userParticipants',
            'attachments',
            'creator'
        ])
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->when($request->agenda_type, fn($q) => $q->where('agenda_type', $request->agenda_type))
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
            'agenda_type' => 'required|string',
            'activity_type' => 'required|string',
            'metting_link' => 'nullable|url',
            'location' => 'required|string',
            'room_id' => 'nullable|exists:rooms,id',
            'status' => 'required|in:comming_soon,in_progress,schedule_change,completed,cancelled',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:participants,id',
            'user_participant_ids' => 'nullable|array',
            'user_participant_ids.*' => 'exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $validated['ceated_by'] = $request->user()->id;

        $agenda = OfficeAgenda::create($validated);

        // Attach participants
        if ($request->participant_ids) {
            $agenda->participants()->attach($request->participant_ids);
        }
        if ($request->user_participant_ids) {
            $agenda->userParticipants()->attach($request->user_participant_ids);
        }

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $attachment = $agenda->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
                $agenda->attachments()->attach($attachment->id);
            }
        }

        return response()->json([
            'message' => 'Office agenda created successfully',
            'agenda' => $agenda->load(['room', 'participants', 'userParticipants', 'attachments']),
        ], 201);
    }

    public function show($id)
    {
        $agenda = OfficeAgenda::with([
            'room',
            'participants',
            'userParticipants',
            'attachments',
            'creator'
        ])->findOrFail($id);

        return response()->json($agenda);
    }

    public function update(Request $request, $id)
    {
        $agenda = OfficeAgenda::findOrFail($id);

        $validated = $request->validate([
            'start_at' => 'sometimes|date',
            'until_at' => 'sometimes|date|after:start_at',
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'agenda_type' => 'sometimes|string',
            'activity_type' => 'sometimes|string',
            'metting_link' => 'nullable|url',
            'location' => 'sometimes|string',
            'room_id' => 'nullable|exists:rooms,id',
            'status' => 'sometimes|in:comming_soon,in_progress,schedule_change,completed,cancelled',
            'participant_ids' => 'nullable|array',
            'user_participant_ids' => 'nullable|array',
        ]);

        $agenda->update($validated);

        // Sync participants
        if ($request->has('participant_ids')) {
            $agenda->participants()->sync($request->participant_ids);
        }
        if ($request->has('user_participant_ids')) {
            $agenda->userParticipants()->sync($request->user_participant_ids);
        }

        return response()->json([
            'message' => 'Office agenda updated successfully',
            'agenda' => $agenda->load(['room', 'participants', 'userParticipants', 'attachments']),
        ]);
    }

    public function destroy($id)
    {
        $agenda = OfficeAgenda::findOrFail($id);
        $agenda->delete();

        return response()->json([
            'message' => 'Office agenda deleted successfully',
        ]);
    }
}
