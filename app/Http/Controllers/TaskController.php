<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\SaveTaskRequest;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $status = $request->query('status', 'pending');

        if (! in_array($status, ['all', 'pending', 'completed'], true)) {
            $status = 'pending';
        }

        $tasksQuery = $request->user()->tasks();

        if ($status !== 'all') {
            $tasksQuery->where('status', $status);
        }

        $tasks = $tasksQuery
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('dashboard', [
            'tasks' => $tasks,
            'status' => $status,
            'pendingCount' => $request->user()->tasks()->where('status', 'pending')->count(),
            'completedCount' => $request->user()->tasks()->where('status', 'completed')->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'task' => new Task,
            'reminderChannels' => [],
            'remindAt' => null,
        ]);
    }

    public function store(SaveTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $channels = $validated['reminder_channels'] ?? [];
        $remindAt = $validated['remind_at'] ?? null;
        unset($validated['reminder_channels'], $validated['remind_at']);

        DB::transaction(function () use ($request, $validated, $channels, $remindAt): void {
            $task = $request->user()->tasks()->create($validated);
            $this->syncReminders($task, $channels, $remindAt);
        });

        return redirect()->route('tasks.index')->with('status', '任务已添加。');
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $task->load('reminders');
        $firstReminder = $task->reminders->sortBy('remind_at')->first();

        return view('tasks.edit', [
            'task' => $task,
            'reminderChannels' => $task->reminders->pluck('channel')->all(),
            'remindAt' => $firstReminder?->remind_at?->format('Y-m-d\\TH:i'),
        ]);
    }

    public function update(SaveTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);
        $validated = $request->validated();
        $channels = $validated['reminder_channels'] ?? [];
        $remindAt = $validated['remind_at'] ?? null;
        unset($validated['reminder_channels'], $validated['remind_at']);

        DB::transaction(function () use ($task, $validated, $channels, $remindAt): void {
            $task->update($validated);
            $this->syncReminders($task, $channels, $remindAt);
        });

        return redirect()->route('tasks.index')->with('status', '任务已更新。');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')->with('status', '任务已删除。');
    }

    public function toggleCompletion(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => $task->status === 'completed' ? 'pending' : 'completed',
            'completed_at' => $task->status === 'completed' ? null : now(),
        ]);

        return back()->with('status', $task->status === 'completed' ? '任务已标记完成。' : '任务已重新打开。');
    }

    /**
     * @param  array<int, string>  $channels
     */
    private function syncReminders(Task $task, array $channels, ?string $remindAt): void
    {
        if ($remindAt === null || $channels === []) {
            $task->reminders()->delete();

            return;
        }

        $scheduledAt = Carbon::parse($remindAt);
        $existingReminders = $task->reminders()->get()->keyBy('channel');

        foreach ($existingReminders as $channel => $reminder) {
            if (! in_array($channel, $channels, true)) {
                $reminder->delete();
            }
        }

        foreach ($channels as $channel) {
            $reminder = $existingReminders->get($channel);

            if ($reminder === null) {
                $task->reminders()->create([
                    'channel' => $channel,
                    'remind_at' => $scheduledAt,
                ]);

                continue;
            }

            if ($reminder->remind_at->format('Y-m-d H:i:s') !== $scheduledAt->format('Y-m-d H:i:s')) {
                $reminder->update([
                    'remind_at' => $scheduledAt,
                    'sent_at' => null,
                    'shown_at' => null,
                ]);
            }
        }
    }
}
