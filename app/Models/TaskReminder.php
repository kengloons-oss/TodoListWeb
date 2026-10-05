<?php

namespace App\Models;

use Database\Factories\TaskReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['channel', 'remind_at', 'sent_at', 'shown_at'])]
class TaskReminder extends Model
{
    /** @use HasFactory<TaskReminderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'sent_at' => 'datetime',
            'shown_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
