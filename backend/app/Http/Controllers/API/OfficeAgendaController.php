<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OfficeAgenda;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficeAgendaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = OfficeAgenda::query();

            // Eager load dengan pengecekan relasi
            $query->with([
                'room:id,name,capacity',
                'creator:id,name,email'
            ]);

            // Load participants dengan pengecekan
            $query->with(['participants' => function($q) {
                $q->select('participants.id', 'participants.name', 'participants.email', 'participants.organization');
            }]);

            $query->with(['userParticipants' => function($q) {
                $q->select('users.id', 'users.name', 'users.email');
            }]);

            $query->with(['attachments' => function($q) {
                $q->select('attachments.id', 'attachments.file_name', 'attachments.file_path', 'attachments.file_type', 'attachments.file_size');
            }]);

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('start_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('start_at', '<=', $request->end_date);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by agenda type
            if ($request->has('agenda_type')) {
                $query->where('agenda_type', $request->agenda_type);
            }

            $perPage = $request->get('per_page', 15);

            if ($perPage == 500) {
                // Return all without pagination
                $agendas = $query->orderBy('start_at', 'desc')->get();
                return response()->json([
                    'data' => $agendas
                ]);
            }

            $agendas = $query->orderBy('start_at', 'desc')->paginate($perPage);

            return response()->json($agendas);
        } catch (\Exception $e) {
            Log::error('Error loading agendas: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to load agendas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function store(Request $request)
    // {
    //     try {
    //         Log::info('Creating agenda with data:', $request->all());

    //         $validated = $request->validate([
    //             'title' => 'required|string|max:255',
    //             'start_at' => 'required|date',
    //             'until_at' => 'required|date|after:start_at',
    //             'agenda_type' => 'required|string|in:meeting,event,training,conference,other',
    //             'activity_type' => 'required|string|in:online,offline,hybrid',
    //             'location' => 'required|string|max:255',
    //             'room_id' => 'nullable|exists:rooms,id',
    //             'metting_link' => 'nullable|url|max:500',
    //             'description' => 'nullable|string',
    //             'status' => 'required|string|in:comming_soon,in_progress,schedule_change,completed,cancelled',
    //             'participant_ids' => 'nullable|array',
    //             'participant_ids.*' => 'exists:participants,id',
    //             'user_participant_ids' => 'required|array|min:1',
    //             'user_participant_ids.*' => 'exists:users,id',
    //             'attachments' => 'nullable|array',
    //             'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
    //         ]);

    //         DB::beginTransaction();

    //         // Create agenda
    //         $agenda = OfficeAgenda::create([
    //             'title' => $validated['title'],
    //             'start_at' => $validated['start_at'],
    //             'until_at' => $validated['until_at'],
    //             'agenda_type' => $validated['agenda_type'],
    //             'activity_type' => $validated['activity_type'],
    //             'location' => $validated['location'],
    //             'room_id' => $validated['room_id'] ?? null,
    //             'metting_link' => $validated['metting_link'] ?? null,
    //             'description' => $validated['description'] ?? null,
    //             'status' => $validated['status'],
    //             'created_by' => auth()->id(),
    //         ]);

    //         Log::info('Agenda created with ID: ' . $agenda->id);

    //         // Attach external participants
    //         if (!empty($validated['participant_ids'])) {
    //             foreach ($validated['participant_ids'] as $participantId) {
    //                 DB::table('office_agenda_participant')->insert([
    //                     'office_agenda_id' => $agenda->id,
    //                     'participant_id' => $participantId,
    //                     'user_id' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //             Log::info('External participants attached');
    //         }

    //         // Attach internal participants (users) - REQUIRED
    //         foreach ($validated['user_participant_ids'] as $userId) {
    //             DB::table('office_agenda_participant')->insert([
    //                 'office_agenda_id' => $agenda->id,
    //                 'participant_id' => null,
    //                 'user_id' => $userId,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //         Log::info('Internal participants attached');

    //         // Handle file uploads
    //         if ($request->hasFile('attachments')) {
    //             Log::info('Processing file uploads');
    //             $attachmentIds = $this->handleFileUploads($request->file('attachments'));

    //             foreach ($attachmentIds as $attachmentId) {
    //                 DB::table('office_agenda_attachment')->insert([
    //                     'office_agenda_id' => $agenda->id,
    //                     'attachment_id' => $attachmentId,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //             Log::info('Attachments uploaded and linked');
    //         }

    //         DB::commit();

    //         // Load relationships
    //         $agenda->load([
    //             'room:id,name,capacity',
    //             'participants:id,name,email,organization',
    //             'userParticipants:id,name,email',
    //             'attachments:id,file_name,file_path,file_type,file_size',
    //             'creator:id,name,email'
    //         ]);

    //         return response()->json([
    //             'message' => 'Agenda created successfully',
    //             'data' => $agenda
    //         ], 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         DB::rollBack();
    //         Log::error('Validation error: ' . json_encode($e->errors()));

    //         return response()->json([
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Failed to create agenda: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());

    //         return response()->json([
    //             'message' => 'Failed to create agenda',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    // app/Http/Controllers/API/OfficeAgendaController.php

public function store(Request $request)
{
    try {
        Log::info('=== START CREATE AGENDA ===');
        Log::info('Request all data:', $request->all());
        Log::info('Request files:', $request->allFiles());

        // Validasi dengan pesan error yang jelas
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'until_at' => 'required|date|after:start_at',
            'agenda_type' => 'required|string|in:meeting,event,training,conference,other',
            'activity_type' => 'required|string|in:online,offline,hybrid',
            'location' => 'required|string|max:255',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'metting_link' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'status' => 'required|string|in:comming_soon,in_progress,schedule_change,completed,cancelled',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'integer|exists:participants,id',
            'user_participant_ids' => 'required|array|min:1',
            'user_participant_ids.*' => 'integer|exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        Log::info('Validation passed');

        DB::beginTransaction();

        // Create agenda dengan data yang sudah divalidasi
        $agendaData = [
            'title' => $validated['title'],
            'start_at' => $validated['start_at'],
            'until_at' => $validated['until_at'],
            'agenda_type' => $validated['agenda_type'],
            'activity_type' => $validated['activity_type'],
            'location' => $validated['location'],
            'status' => $validated['status'],
            'created_by' => auth()->id(),
        ];

        // Tambahkan optional fields jika ada
        if (isset($validated['room_id'])) {
            $agendaData['room_id'] = $validated['room_id'];
        }
        if (isset($validated['metting_link'])) {
            $agendaData['metting_link'] = $validated['metting_link'];
        }
        if (isset($validated['description'])) {
            $agendaData['description'] = $validated['description'];
        }

        Log::info('Creating agenda with data:', $agendaData);

        $agenda = OfficeAgenda::create($agendaData);

        Log::info('Agenda created with ID: ' . $agenda->id);

        // Attach external participants
        if (!empty($validated['participant_ids'])) {
            foreach ($validated['participant_ids'] as $participantId) {
                DB::table('office_agenda_participant')->insert([
                    'office_agenda_id' => $agenda->id,
                    'participant_id' => $participantId,
                    'user_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            Log::info('External participants attached: ' . count($validated['participant_ids']));
        }

        // Attach internal participants (users) - REQUIRED
        foreach ($validated['user_participant_ids'] as $userId) {
            DB::table('office_agenda_participant')->insert([
                'office_agenda_id' => $agenda->id,
                'participant_id' => null,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        Log::info('Internal participants attached: ' . count($validated['user_participant_ids']));

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            Log::info('Processing ' . count($request->file('attachments')) . ' file uploads');
            $attachmentIds = $this->handleFileUploads($request->file('attachments'));

            foreach ($attachmentIds as $attachmentId) {
                DB::table('office_agenda_attachment')->insert([
                    'office_agenda_id' => $agenda->id,
                    'attachment_id' => $attachmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            Log::info('Attachments uploaded and linked: ' . count($attachmentIds));
        }

        DB::commit();
        Log::info('Transaction committed successfully');

        // Load relationships
        $agenda->load([
            'room:id,name,capacity',
            'participants:id,name,email,organization',
            'userParticipants:id,name,email',
            'attachments:id,file_name,file_path,file_type,file_size',
            'creator:id,name,email'
        ]);

        Log::info('=== END CREATE AGENDA SUCCESS ===');

        return response()->json([
            'message' => 'Agenda created successfully',
            'data' => $agenda
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        Log::error('Validation error:', $e->errors());

        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('=== ERROR CREATE AGENDA ===');
        Log::error('Error message: ' . $e->getMessage());
        Log::error('Error file: ' . $e->getFile() . ' line ' . $e->getLine());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'message' => 'Failed to create agenda',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}

    public function show($id)
    {
        try {
            $agenda = OfficeAgenda::with([
                'room:id,name,capacity',
                'participants:id,name,email,organization',
                'userParticipants:id,name,email',
                'attachments:id,file_name,file_path,file_type,file_size',
                'creator:id,name,email'
            ])->findOrFail($id);

            return response()->json($agenda);
        } catch (\Exception $e) {
            Log::error('Error loading agenda: ' . $e->getMessage());

            return response()->json([
                'message' => 'Agenda not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $officeAgenda = OfficeAgenda::findOrFail($id);

            Log::info('Updating agenda ID: ' . $id);
            Log::info('Update data:', $request->all());

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'start_at' => 'sometimes|date',
                'until_at' => 'sometimes|date|after:start_at',
                'agenda_type' => 'sometimes|string|in:meeting,event,training,conference,other',
                'activity_type' => 'sometimes|string|in:online,offline,hybrid',
                'location' => 'sometimes|string|max:255',
                'room_id' => 'nullable|exists:rooms,id',
                'metting_link' => 'nullable|url|max:500',
                'description' => 'nullable|string',
                'status' => 'sometimes|string|in:comming_soon,in_progress,schedule_change,completed,cancelled',
                'participant_ids' => 'nullable|array',
                'participant_ids.*' => 'exists:participants,id',
                'user_participant_ids' => 'sometimes|array|min:1',
                'user_participant_ids.*' => 'exists:users,id',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
            ]);

            DB::beginTransaction();

            // Update agenda basic info
            $updateData = [];
            if (isset($validated['title'])) $updateData['title'] = $validated['title'];
            if (isset($validated['start_at'])) $updateData['start_at'] = $validated['start_at'];
            if (isset($validated['until_at'])) $updateData['until_at'] = $validated['until_at'];
            if (isset($validated['agenda_type'])) $updateData['agenda_type'] = $validated['agenda_type'];
            if (isset($validated['activity_type'])) $updateData['activity_type'] = $validated['activity_type'];
            if (isset($validated['location'])) $updateData['location'] = $validated['location'];
            if (array_key_exists('room_id', $validated)) $updateData['room_id'] = $validated['room_id'];
            if (array_key_exists('metting_link', $validated)) $updateData['metting_link'] = $validated['metting_link'];
            if (array_key_exists('description', $validated)) $updateData['description'] = $validated['description'];
            if (isset($validated['status'])) $updateData['status'] = $validated['status'];

            $officeAgenda->update($updateData);

            // Update external participants
            if (isset($validated['participant_ids'])) {
                DB::table('office_agenda_participant')
                    ->where('office_agenda_id', $officeAgenda->id)
                    ->whereNotNull('participant_id')
                    ->delete();

                foreach ($validated['participant_ids'] as $participantId) {
                    DB::table('office_agenda_participant')->insert([
                        'office_agenda_id' => $officeAgenda->id,
                        'participant_id' => $participantId,
                        'user_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Update internal participants (users)
            if (isset($validated['user_participant_ids'])) {
                DB::table('office_agenda_participant')
                    ->where('office_agenda_id', $officeAgenda->id)
                    ->whereNotNull('user_id')
                    ->delete();

                foreach ($validated['user_participant_ids'] as $userId) {
                    DB::table('office_agenda_participant')->insert([
                        'office_agenda_id' => $officeAgenda->id,
                        'participant_id' => null,
                        'user_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Handle new file uploads
            if ($request->hasFile('attachments')) {
                $attachmentIds = $this->handleFileUploads($request->file('attachments'));

                foreach ($attachmentIds as $attachmentId) {
                    DB::table('office_agenda_attachment')->insert([
                        'office_agenda_id' => $officeAgenda->id,
                        'attachment_id' => $attachmentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $officeAgenda->load([
                'room:id,name,capacity',
                'participants:id,name,email,organization',
                'userParticipants:id,name,email',
                'attachments:id,file_name,file_path,file_type,file_size',
                'creator:id,name,email'
            ]);

            return response()->json([
                'message' => 'Agenda updated successfully',
                'data' => $officeAgenda
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update agenda: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update agenda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $officeAgenda = OfficeAgenda::findOrFail($id);
            $officeAgenda->delete();

            return response()->json([
                'message' => 'Agenda deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete agenda: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete agenda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function handleFileUploads($files)
    {
        $attachmentIds = [];

        foreach ($files as $file) {
            try {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('attachments/agendas', $fileName, 'public');

                $attachment = Attachment::create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                $attachmentIds[] = $attachment->id;
            } catch (\Exception $e) {
                Log::error('Failed to upload file: ' . $e->getMessage());
                continue;
            }
        }

        return $attachmentIds;
    }

    public function deleteAttachment(Request $request, $officeAgendaId)
    {
        $validated = $request->validate([
            'attachment_id' => 'required|exists:attachments,id'
        ]);

        try {
            $attachment = Attachment::findOrFail($validated['attachment_id']);

            DB::table('office_agenda_attachment')
                ->where('office_agenda_id', $officeAgendaId)
                ->where('attachment_id', $attachment->id)
                ->delete();

            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->delete();

            return response()->json([
                'message' => 'Attachment deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete attachment: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
