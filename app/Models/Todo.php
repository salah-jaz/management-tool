<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'priority',
        'description',
        'creator_id',
        'creator_type',
        'completed',
        'workspace_id'
    ];
    public function creator()
    {
        return $this->morphTo();
    }

    public function reminders()
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }
}
