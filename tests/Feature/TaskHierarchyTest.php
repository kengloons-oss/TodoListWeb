<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_subtask_to_a_main_task(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->post(route('tasks.subtasks.store', $mainTask), [
                'title' => 'Draft the outline',
                'description' => 'Write the first version.',
            ]);

        $response->assertSessionHas('status', '子任务已添加。');
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'parent_task_id' => $mainTask->id,
            'title' => 'Draft the outline',
            'description' => 'Write the first version.',
            'status' => 'pending',
        ]);
    }

    public function test_main_task_cannot_be_completed_while_a_subtask_is_pending(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create();
        $subtask = Task::factory()->for($user)->for($mainTask, 'parentTask')->create();

        $response = $this->actingAs($user)
            ->patch(route('tasks.toggle-completion', $mainTask));

        $response->assertSessionHasErrors('subtasks');
        $this->assertDatabaseHas('tasks', [
            'id' => $mainTask->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $subtask->id,
            'status' => 'pending',
        ]);
    }

    public function test_main_task_can_be_completed_when_all_subtasks_are_completed_or_skipped(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create();
        Task::factory()->for($user)->for($mainTask, 'parentTask')->create([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        Task::factory()->for($user)->for($mainTask, 'parentTask')->create([
            'status' => 'skipped',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('tasks.toggle-completion', $mainTask));

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $mainTask->id,
            'status' => 'completed',
        ]);
    }

    public function test_skip_only_marks_the_subtask_skipped_without_creating_a_task(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create();
        $subtask = Task::factory()->for($user)->for($mainTask, 'parentTask')->create();

        $response = $this->actingAs($user)
            ->patch(route('tasks.subtasks.skip', [$mainTask, $subtask]), ['decision' => 'skip']);

        $response->assertSessionHas('status', '子任务已跳过。');
        $this->assertDatabaseHas('tasks', [
            'id' => $subtask->id,
            'status' => 'skipped',
        ]);
        $this->assertDatabaseCount('tasks', 2);
    }

    public function test_skipping_and_converting_a_subtask_creates_a_standalone_task_with_default_dates(): void
    {
        $this->travelTo('2026-10-08 12:00:00');
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create();
        $subtask = Task::factory()->for($user)->for($mainTask, 'parentTask')->create([
            'title' => 'Prepare the report',
            'description' => 'Collect the latest numbers.',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('tasks.subtasks.skip', [$mainTask, $subtask]), ['decision' => 'skip_and_create']);

        $response->assertSessionHas('status', '子任务已跳过，并已建立新的独立任务。');
        $this->assertDatabaseHas('tasks', [
            'id' => $subtask->id,
            'status' => 'skipped',
            'parent_task_id' => $mainTask->id,
        ]);
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'parent_task_id' => null,
            'title' => 'Prepare the report',
            'description' => 'Collect the latest numbers.',
            'priority' => 'high',
            'status' => 'pending',
            'due_at' => '2026-10-16 23:59:59',
        ]);
        $createdTask = Task::query()
            ->where('title', 'Prepare the report')
            ->whereNull('parent_task_id')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('2026-10-09', $createdTask->starts_on->toDateString());
        $this->assertSame('2026-10-16 23:59:59', $createdTask->due_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseCount('tasks', 3);
    }

    public function test_reopening_a_completed_subtask_also_reopens_its_completed_main_task(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $subtask = Task::factory()->for($user)->for($mainTask, 'parentTask')->create([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->patch(route('tasks.subtasks.toggle-completion', [$mainTask, $subtask]));

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $mainTask->id,
            'status' => 'pending',
            'completed_at' => null,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $subtask->id,
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    public function test_restoring_a_skipped_subtask_reopens_its_completed_main_task(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $subtask = Task::factory()->for($user)->for($mainTask, 'parentTask')->create([
            'status' => 'skipped',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('tasks.subtasks.restore', [$mainTask, $subtask]));

        $response->assertSessionHas('status', '子任务已恢复为待完成。');
        $this->assertDatabaseHas('tasks', [
            'id' => $mainTask->id,
            'status' => 'pending',
            'completed_at' => null,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $subtask->id,
            'status' => 'pending',
        ]);
    }

    public function test_subtasks_cannot_be_nested_under_another_subtask(): void
    {
        $user = User::factory()->create();
        $mainTask = Task::factory()->for($user)->create();
        $subtask = Task::factory()->for($user)->for($mainTask, 'parentTask')->create();

        $response = $this->actingAs($user)
            ->post(route('tasks.subtasks.store', $subtask), ['title' => 'Nested subtask']);

        $response->assertForbidden();
        $this->assertDatabaseCount('tasks', 2);
    }
}
