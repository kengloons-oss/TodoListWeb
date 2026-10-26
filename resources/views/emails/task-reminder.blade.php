<x-mail::message>
# 任务提醒

你设置的任务提醒时间已到。

**任务：** {{ $reminder->task->title }}

@if ($reminder->task->due_at)
**截止时间：** {{ $reminder->task->due_at->format('Y-m-d H:i') }}
@endif

@if ($reminder->task->description)
**备注：** {{ $reminder->task->description }}
@endif

<x-mail::button :url="route('tasks.index')">
打开 TodoListWeb
</x-mail::button>

谢谢，
{{ config('app.name') }}
</x-mail::message>
