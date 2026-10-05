@extends('layouts.app')

@section('title', '编辑任务 · '.config('app.name', 'TodoListWeb'))

@section('content')
    <main class="mx-auto min-h-screen w-full max-w-3xl px-5 py-8 sm:px-8 sm:py-12">
        <a class="text-sm font-medium text-emerald-800 hover:underline dark:text-emerald-400" href="{{ route('tasks.index') }}">← 返回任务列表</a>
        <div class="mt-8">
            <h1 class="text-3xl font-semibold tracking-tight">编辑任务</h1>
            <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">更新任务内容、优先级或截止时间。</p>
        </div>
        <section class="mt-8 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8 dark:border-stone-800 dark:bg-stone-900">
            @include('tasks.form')
        </section>
    </main>
@endsection
