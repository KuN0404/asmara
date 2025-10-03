<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::with(['attachments', 'creator'])
            ->when($request->is_displayed !== null, fn($q) => $q->where('is_displayed', $request->is_displayed))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'media' => 'nullable|file|max:10240',
            'is_displayed' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $validated['created_by'] = $request->user()->id;

        if ($request->hasFile('media')) {
            $validated['media'] = $request->file('media')->store('announcements', 'public');
        }

        $announcement = Announcement::create($validated);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $attachment = $announcement->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
                $announcement->attachments()->attach($attachment->id);
            }
        }

        return response()->json([
            'message' => 'Announcement created successfully',
            'announcement' => $announcement->load('attachments'),
        ], 201);
    }

    public function show($id)
    {
        $announcement = Announcement::with(['attachments', 'creator'])->findOrFail($id);
        return response()->json($announcement);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'media' => 'nullable|file|max:10240',
            'is_displayed' => 'boolean',
        ]);

        if ($request->hasFile('media')) {
            if ($announcement->media) {
                Storage::disk('public')->delete($announcement->media);
            }
            $validated['media'] = $request->file('media')->store('announcements', 'public');
        }

        $announcement->update($validated);

        return response()->json([
            'message' => 'Announcement updated successfully',
            'announcement' => $announcement->load('attachments'),
        ]);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        if ($announcement->media) {
            Storage::disk('public')->delete($announcement->media);
        }

        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully',
        ]);
    }
}
