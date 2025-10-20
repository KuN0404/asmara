<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeAgenda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'start_at',
        'until_at',
        'agenda_type',
        'activity_type',
        'metting_link',
        'location',
        'room_id',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'until_at' => 'datetime',
    ];

    // Relasi dengan Room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relasi dengan Participants (External)
    public function participants()
    {
        return $this->belongsToMany(
            Participant::class,
            'office_agenda_participant',
            'office_agenda_id',
            'participant_id'
        )->whereNotNull('participant_id');
    }

    // Relasi dengan Users (Internal Participants)
    public function userParticipants()
    {
        return $this->belongsToMany(
            User::class,
            'office_agenda_participant',
            'office_agenda_id',
            'user_id'
        )->whereNotNull('user_id');
    }

    // Relasi dengan Attachments
    public function attachments()
    {
        return $this->belongsToMany(
            Attachment::class,
            'office_agenda_attachment',
            'office_agenda_id',
            'attachment_id'
        );
    }

    // Relasi dengan Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Auto set deleted_at when status is cancelled
    protected static function booted()
    {
        static::updating(function ($agenda) {
            if ($agenda->isDirty('status') && $agenda->status === 'cancelled') {
                $agenda->deleted_at = now();
            }
        });
    }
}
