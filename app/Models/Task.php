<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'status', 'priority', 'starts_on', 'due_at', 'completed_at', 'parent_task_id'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'starts_on' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(TaskReminder::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_task_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }
}
