<?php

namespace App\Http\Controllers;

use App\Models\TaskReminder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->getAuthIdentifier();

        $reminders = TaskReminder::query()
            ->with('task:id,user_id,title')
            ->whereNull('sent_at')
            ->whereNull('shown_at')
            ->where('remind_at', '<=', now())
            ->whereHas('task', function (Builder $query) use ($userId): void {
                $query->where('user_id', $userId)->where('status', 'pending');
            })
            ->orderBy('remind_at')
            ->limit(25)
            ->get()
            ->map(static fn (TaskReminder $reminder): array => [
                'id' => $reminder->id,
                'task_id' => $reminder->task_id,
                'title' => $reminder->task->title,
                'remind_at' => $reminder->remind_at->toIso8601String(),
                'acknowledgement_url' => route('reminders.shown', $reminder),
            ])
            ->values();

        return response()->json(['reminders' => $reminders]);
    }

    public function markShown(Request $request, TaskReminder $reminder): JsonResponse
    {
        abort_unless(
            $reminder->task()->where('user_id', $request->user()->getAuthIdentifier())->exists(),
            404,
        );

        if ($reminder->shown_at === null) {
            $reminder->update(['shown_at' => now()]);
        }

        return response()->json(['acknowledged' => true]);
    }
}
