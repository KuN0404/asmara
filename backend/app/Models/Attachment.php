<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function officeAgendas()
    {
        return $this->belongsToMany(OfficeAgenda::class, 'office_agenda_attachment');
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_attachment');
    }
}