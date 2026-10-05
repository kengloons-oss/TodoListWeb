@extends('layouts.app')

@section('title', '创建账号 · '.config('app.name', 'TodoListWeb'))

@section('content')
    <main class="grid min-h-screen place-items-center px-5 py-12">
        <div class="w-full max-w-md">
            <a class="mb-8 flex items-center justify-center gap-3 font-semibold tracking-tight" href="{{ route('register') }}">
                <span class="grid size-10 place-items-center rounded-2xl bg-emerald-700 text-white" aria-hidden="true">✓</span>
                <span class="text-lg">TodoList<span class="text-emerald-700 dark:text-emerald-400">Web</span></span>
            </a>
            <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8 dark:border-stone-800 dark:bg-stone-900">
                <h1 class="text-2xl font-semibold tracking-tight">创建你的账号</h1>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">开始整理任务，数据保存在当前应用数据库中。</p>

                <form class="mt-7 space-y-5" method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="name">姓名</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
                        @error('name') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="email">电子邮件</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                        @error('email') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="password">密码（至少 8 位）</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                        @error('password') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="password_confirmation">确认密码</label>
                        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    </div>
                    <button class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" type="submit">创建账号</button>
                </form>
                <p class="mt-6 text-center text-sm text-stone-600 dark:text-stone-400">已经有账号？ <a class="font-semibold text-emerald-800 hover:underline dark:text-emerald-400" href="{{ route('login') }}">登录</a></p>
            </section>
        </div>
    </main>
@endsection
