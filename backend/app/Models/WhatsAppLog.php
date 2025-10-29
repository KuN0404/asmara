<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppLog extends Model
{
    protected $table = 'whatsapp_logs';

    protected $fillable = [
        'phone_number',
        'message',
        'type',
        'trigger',
        'related_id',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get related office agenda
     */
    public function officeAgenda()
    {
        return $this->belongsTo(OfficeAgenda::class, 'related_id');
    }

    /**
     * Get related my agenda
     */
    public function myAgenda()
    {
        return $this->belongsTo(MyAgenda::class, 'related_id');
    }

    /**
     * Get related announcement
     */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'related_id');
    }
}