<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcement';

    protected $fillable = [
        'title',
        'content',
        'is_displayed',
        'created_by',
    ];

    protected $casts = [
        'is_displayed' => 'boolean',
    ];

    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'announcement_attachment');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}