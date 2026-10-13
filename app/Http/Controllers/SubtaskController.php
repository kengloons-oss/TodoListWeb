<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\StoreSubtaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubtaskController extends Controller
{
    public function store(StoreSubtaskRequest $request, Task $task): RedirectResponse
    {
        $created = DB::transaction(function () use ($request, $task): bool {
            $parentTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());

            if ($parentTask->status === 'completed') {
                return false;
            }

            $parentTask->user->tasks()->create([
                ...$request->validated(),
                'parent_task_id' => $parentTask->getKey(),
            ]);

            return true;
        });

        if (! $created) {
            return back()->withErrors([
                'subtasks' => '请先重新打开主任务，再添加子任务。',
            ]);
        }

        return back()->with('status', '子任务已添加。');
    }

    public function toggleCompletion(Task $task, Task $subtask): RedirectResponse
    {
        $this->authorize('update', $task);
        $this->authorize('update', $subtask);
        abort_if($task->parent_task_id !== null, 404);

        $status = DB::transaction(function () use ($task, $subtask): string {
            $parentTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            $subtask = $parentTask->subtasks()->lockForUpdate()->findOrFail($subtask->getKey());

            if ($subtask->status === 'skipped') {
                throw ValidationException::withMessages([
                    'subtasks' => '已跳过的子任务请使用“恢复”操作。',
                ]);
            }

            $status = $subtask->status === 'completed' ? 'pending' : 'completed';

            $subtask->update([
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            if ($status === 'pending' && $parentTask->status === 'completed') {
                $parentTask->update([
                    'status' => 'pending',
                    'completed_at' => null,
                ]);
            }

            return $status;
        });

        return back()->with('status', $status === 'completed' ? '子任务已完成。' : '子任务已重新打开；主任务也已重新打开。');
    }

    public function skip(Request $request, Task $task, Task $subtask): RedirectResponse
    {
        $this->authorize('update', $task);
        $this->authorize('update', $subtask);
        abort_if($task->parent_task_id !== null, 404);

        $validated = $request->validate([
            'decision' => ['required', 'in:skip,skip_and_create'],
        ]);

        $createdTask = DB::transaction(function () use ($task, $subtask, $validated): ?Task {
            $parentTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            $subtask = $parentTask->subtasks()->lockForUpdate()->findOrFail($subtask->getKey());

            if ($subtask->status !== 'pending') {
                throw ValidationException::withMessages([
                    'subtasks' => '只有待完成的子任务可以跳过。',
                ]);
            }

            $subtask->update([
                'status' => 'skipped',
                'completed_at' => null,
            ]);

            if ($validated['decision'] === 'skip') {
                return null;
            }

            $startsOn = today()->addDay();

            return $parentTask->user->tasks()->create([
                'title' => $subtask->title,
                'description' => $subtask->description,
                'priority' => $subtask->priority,
                'starts_on' => $startsOn,
                'due_at' => $startsOn->copy()->addDays(7)->endOfDay(),
                'status' => 'pending',
            ]);
        });

        return back()->with(
            'status',
            $createdTask === null ? '子任务已跳过。' : '子任务已跳过，并已建立新的独立任务。'
        );
    }

    public function restore(Task $task, Task $subtask): RedirectResponse
    {
        $this->authorize('update', $task);
        $this->authorize('update', $subtask);
        abort_if($task->parent_task_id !== null, 404);

        DB::transaction(function () use ($task, $subtask): void {
            $parentTask = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            $subtask = $parentTask->subtasks()->lockForUpdate()->findOrFail($subtask->getKey());

            if ($subtask->status !== 'skipped') {
                throw ValidationException::withMessages([
                    'subtasks' => '只有已跳过的子任务可以恢复。',
                ]);
            }

            $subtask->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);

            if ($parentTask->status === 'completed') {
                $parentTask->update([
                    'status' => 'pending',
                    'completed_at' => null,
                ]);
            }
        });

        return back()->with('status', '子任务已恢复为待完成。');
    }
}
