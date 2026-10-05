<form class="space-y-6" method="POST" action="{{ $task->exists ? route('tasks.update', $task) : route('tasks.store') }}">
    @csrf
    @if ($task->exists)
        @method('PUT')
    @endif

    <div>
        <label class="mb-2 block text-sm font-medium" for="title">任务名称</label>
        <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="title" name="title" type="text" value="{{ old('title', $task->title) }}" maxlength="160" required autofocus>
        @error('title') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium" for="description">备注 <span class="font-normal text-stone-500">（选填）</span></label>
        <textarea class="min-h-28 w-full resize-y rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="description" name="description" rows="4" maxlength="5000">{{ old('description', $task->description) }}</textarea>
        @error('description') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-medium" for="priority">优先级</label>
            <select class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="priority" name="priority" required>
                <option value="high" @selected(old('priority', $task->priority ?? 'normal') === 'high')>高</option>
                <option value="normal" @selected(old('priority', $task->priority ?? 'normal') === 'normal')>普通</option>
                <option value="low" @selected(old('priority', $task->priority ?? 'normal') === 'low')>低</option>
            </select>
            @error('priority') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium" for="due_at">截止时间 <span class="font-normal text-stone-500">（选填）</span></label>
            <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', $task->due_at?->format('Y-m-d\TH:i')) }}">
            @error('due_at') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
        </div>
    </div>

    <fieldset class="rounded-2xl border border-stone-200 p-4 sm:p-5 dark:border-stone-700">
        <legend class="px-1 text-sm font-semibold">提醒</legend>
        <div class="mt-2">
            <label class="mb-2 block text-sm font-medium" for="remind_at">提醒时间</label>
            <input class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/15 dark:border-stone-700 dark:bg-stone-950" id="remind_at" name="remind_at" type="datetime-local" value="{{ old('remind_at', $remindAt) }}">
            @error('remind_at') <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
        </div>
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-3">
            <label class="flex items-center gap-2 text-sm text-stone-700 dark:text-stone-300">
                <input class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-700" name="reminder_channels[]" type="checkbox" value="email" @checked(in_array('email', old('reminder_channels', $reminderChannels), true))>
                邮件 <span class="text-xs text-stone-500">（Hosting 后启用）</span>
            </label>
            <label class="flex items-center gap-2 text-sm text-stone-700 dark:text-stone-300">
                <input class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-700" name="reminder_channels[]" type="checkbox" value="push" @checked(in_array('push', old('reminder_channels', $reminderChannels), true))>
                手机推送 <span class="text-xs text-stone-500">（Hosting 后启用）</span>
            </label>
        </div>
        @error('reminder_channels') <p class="mt-3 text-sm text-rose-700 dark:text-rose-300">{{ $message }}</p> @enderror
        <p class="mt-3 text-xs leading-5 text-stone-500 dark:text-stone-400">设置后，应用保持打开时会在提醒时间显示站内提示。邮件和手机推送会在 Hosting 后接入。</p>
    </fieldset>

    <div class="flex flex-col-reverse gap-3 border-t border-stone-100 pt-5 sm:flex-row sm:justify-end dark:border-stone-800">
        <a class="rounded-xl border border-stone-200 px-4 py-3 text-center text-sm font-medium text-stone-700 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800" href="{{ route('tasks.index') }}">取消</a>
        <button class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" type="submit">{{ $task->exists ? '保存更改' : '创建任务' }}</button>
    </div>
</form>
