<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'whatsapp_number',
        'address',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function officeAgendas()
    {
        return $this->belongsToMany(OfficeAgenda::class, 'office_agenda_participant');
    }

    public function myAgendas()
    {
        return $this->hasMany(MyAgenda::class, 'ceated_by', 'id');
    }
}
