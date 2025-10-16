<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MyAgenda extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'start_at',
        'until_at',
        'title',
        'description',
        'is_show_to_other',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'until_at' => 'datetime',
        'is_show_to_other' => 'boolean',
    ];

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
