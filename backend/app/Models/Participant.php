<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization',
    ];

    public function officeAgendas()
    {
        return $this->belongsToMany(OfficeAgenda::class, 'office_agenda_participant');
    }
}