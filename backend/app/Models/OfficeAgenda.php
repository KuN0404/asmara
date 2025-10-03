<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeAgenda extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'start_at',
        'until_at',
        'title',
        'description',
        'agenda_type',
        'activity_type',
        'metting_link',
        'location',
        'room_id',
        'status',
        'ceated_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'until_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'office_agenda_participant');
    }

    public function userParticipants()
    {
        return $this->belongsToMany(User::class, 'office_agenda_participant');
    }

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'office_agenda_attachment');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'ceated_by');
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