@extends('layouts.app')

@section('title', '我的待办 · '.config('app.name', 'TodoListWeb'))

@section('content')
    <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-5 py-6 sm:px-8 lg:px-10" data-reminder-endpoint="{{ route('reminders.due') }}">
        <header class="flex items-center justify-between border-b border-stone-200 pb-5 dark:border-stone-800">
            <a class="flex items-center gap-3 font-semibold tracking-tight" href="{{ route('tasks.index') }}">
                <span class="grid size-10 place-items-center rounded-2xl bg-emerald-700 text-white shadow-sm shadow-emerald-900/15" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 4 4L19 6" />
                    </svg>
                </span>
                <span class="text-lg">TodoList<span class="text-emerald-700 dark:text-emerald-400">Web</span></span>
            </a>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-stone-600 sm:inline dark:text-stone-300">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-xl border border-stone-200 px-3 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800" type="submit">退出</button>
                </form>
            </div>
        </header>

        <main class="flex flex-1 flex-col py-12 sm:py-16">
            <div class="mb-10 flex flex-col gap-5 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-3 text-sm font-medium text-emerald-800 dark:text-emerald-400">{{ now()->format('Y-m-d') }}</p>
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">把今天理清楚。</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-stone-600 sm:text-base dark:text-stone-400">重要的事情一件一件来。你的待办清单会在这里展开。</p>
                </div>
                <div class="flex items-end gap-3">
                    <div class="min-w-28 rounded-2xl border border-stone-200 bg-white px-4 py-3 dark:border-stone-800 dark:bg-stone-900">
                        <p class="text-xs text-stone-500 dark:text-stone-400">待完成</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $pendingCount }}</p>
                    </div>
                    <div class="min-w-28 rounded-2xl border border-stone-200 bg-white px-4 py-3 dark:border-stone-800 dark:bg-stone-900">
                        <p class="text-xs text-stone-500 dark:text-stone-400">已完成</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $completedCount }}</p>
                    </div>
                    <a class="inline-flex items-center gap-2 rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" href="{{ route('tasks.create') }}">
                        <span aria-hidden="true">+</span> 新建任务
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</div>
            @endif

            <section class="flex flex-1 flex-col overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm shadow-stone-900/[0.03] dark:border-stone-800 dark:bg-stone-900" aria-labelledby="task-list-heading">
                <div class="flex flex-col gap-4 border-b border-stone-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7 dark:border-stone-800">
                    <div>
                        <h2 id="task-list-heading" class="font-semibold">所有任务</h2>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">按截止日期和优先级安排</p>
                    </div>
                    <nav class="flex gap-1 rounded-xl bg-stone-100 p-1 dark:bg-stone-800" aria-label="任务筛选">
                        @foreach (['pending' => '待完成', 'all' => '全部', 'completed' => '已完成'] as $filter => $label)
                            <a class="rounded-lg px-3 py-2 text-xs font-medium transition {{ $status === $filter ? 'bg-white text-stone-900 shadow-sm dark:bg-stone-700 dark:text-white' : 'text-stone-600 hover:text-stone-900 dark:text-stone-300 dark:hover:text-white' }}" href="{{ route('tasks.index', ['status' => $filter]) }}" @if ($status === $filter) aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>

                @if ($tasks->isEmpty())
                    <div class="grid flex-1 place-items-center px-6 py-16 text-center">
                        <div class="max-w-sm">
                            <span class="mx-auto mb-5 grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" class="size-7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                </svg>
                            </span>
                            <h3 class="text-lg font-semibold">{{ $status === 'completed' ? '还没有已完成的任务' : '清单还是空的' }}</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-600 dark:text-stone-400">添加一件要做的事，再一步一步完成它。</p>
                            <a class="mt-5 inline-flex rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800" href="{{ route('tasks.create') }}">添加第一项</a>
                        </div>
                    </div>
                @else
                    <div class="divide-y divide-stone-100 dark:divide-stone-800">
                        @foreach ($tasks as $task)
                            <article class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                                <div class="flex min-w-0 items-start gap-4">
                                    <form method="POST" action="{{ route('tasks.toggle-completion', $task) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full border-2 transition {{ $task->status === 'completed' ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-stone-300 text-transparent hover:border-emerald-700 dark:border-stone-600' }}" type="submit" aria-label="{{ $task->status === 'completed' ? '重新打开任务' : '标记任务完成' }}">
                                            @if ($task->status === 'completed')
                                                <svg viewBox="0 0 16 16" fill="none" class="size-3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 8 3 3 7-7" /></svg>
                                            @endif
                                        </button>
                                    </form>
                                    <div class="min-w-0">
                                        <h3 class="truncate font-medium {{ $task->status === 'completed' ? 'text-stone-400 line-through dark:text-stone-500' : 'text-stone-900 dark:text-stone-100' }}">{{ $task->title }}</h3>
                                        @if ($task->description)
                                            <p class="mt-1 line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $task->description }}</p>
                                        @endif
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-stone-500 dark:text-stone-400">
                                            <span class="rounded-full px-2.5 py-1 font-medium {{ ['high' => 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300', 'normal' => 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300', 'low' => 'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-300'][$task->priority] }}">{{ ['high' => '高优先级', 'normal' => '普通', 'low' => '低优先级'][$task->priority] }}</span>
                                            @if ($task->due_at)
                                                <span @class(['text-rose-700 dark:text-rose-300' => $task->status === 'pending' && $task->due_at->isPast()])>截止 {{ $task->due_at->format('Y-m-d H:i') }}</span>
                                            @else
                                                <span>无截止日期</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-2 pl-10 sm:pl-0">
                                    <a class="rounded-lg px-3 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-100 hover:text-stone-900 dark:text-stone-300 dark:hover:bg-stone-800 dark:hover:text-white" href="{{ route('tasks.edit', $task) }}">编辑</a>
                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" data-confirm="确定删除这项任务吗？">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950" type="submit">删除</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($tasks->hasPages())
                        <div class="border-t border-stone-100 px-5 py-4 dark:border-stone-800">{{ $tasks->links() }}</div>
                    @endif
                @endif
            </section>
        </main>

        <footer class="border-t border-stone-200 py-5 text-xs text-stone-500 dark:border-stone-800 dark:text-stone-400">简单记录，安心去做。</footer>
    </div>
@endsection
