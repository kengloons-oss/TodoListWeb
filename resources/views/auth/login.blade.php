@extends('layouts.app')

@section('title', '登录 · '.config('app.name', 'TodoListWeb'))

@section('content')
    <main class="grid min-h-screen place-items-center px-5 py-12">
        <div class="w-full max-w-md">
            <a class="mb-8 flex items-center justify-center gap-3 font-semibold tracking-tight" href="{{ route('login') }}">
                <span class="grid size-10 place-items-center rounded-2xl bg-emerald-700 text-white" aria-hidden="true">✓</span>
                <span class="text-lg">TodoList<span class="text-emerald-700 dark:text-emerald-400">Web</span></span>
            </a>
            <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8 dark:border-stone-800 dark:bg-stone-900">
                <h1 class="text-2xl font-semibold tracking-tight">欢迎回来</h1>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">登录后继续管理你的待办事项。</p>

                <form class="mt-7 space-y-5" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="email">电子邮件</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @error('email') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="password">密码</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="password" name="password" type="password" autocomplete="current-password" required>
                        @error('password') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-600 dark:text-stone-400">
                        <input class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-700" name="remember" type="checkbox" value="1">
                        记住我
                    </label>
                    <button class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" type="submit">登录</button>
                </form>
                <p class="mt-6 text-center text-sm text-stone-600 dark:text-stone-400">还没有账号？ <a class="font-semibold text-emerald-800 hover:underline dark:text-emerald-400" href="{{ route('register') }}">创建一个</a></p>
            </section>
            <p class="mt-5 text-center text-xs text-stone-500 dark:text-stone-400">账号和任务数据保存在当前应用数据库中。</p>
        </div>
    </main>
@endsection
