<?php

namespace App\Console\Commands;

use App\Mail\TaskReminderMail;
use App\Models\TaskReminder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendDueTaskReminderEmails extends Command
{
    protected $signature = 'reminders:send-due-emails';

    protected $description = 'Send email reminders for due, pending tasks';

    public function handle(): int
    {
        $sentCount = 0;
        $failedCount = 0;

        TaskReminder::query()
            ->with(['task.user'])
            ->where('channel', 'email')
            ->whereNull('sent_at')
            ->where('remind_at', '<=', now())
            ->whereHas('task', static function (Builder $query): void {
                $query->where('status', 'pending');
            })
            ->orderBy('id')
            ->chunkById(100, function (EloquentCollection $reminders) use (&$sentCount, &$failedCount): void {
                foreach ($reminders as $reminder) {
                    $task = $reminder->task;
                    $recipient = $task?->user;

                    if ($task === null || $recipient === null) {
                        continue;
                    }

                    try {
                        Mail::to($recipient->email)->send(new TaskReminderMail($reminder));
                        $reminder->update(['sent_at' => now()]);
                        $sentCount++;
                    } catch (Throwable $exception) {
                        report($exception);
                        $failedCount++;
                        $this->components->error("Could not send reminder #{$reminder->getKey()}; it will be retried on the next scheduler run.");
                    }
                }
            });

        $this->components->info("Sent {$sentCount} due task reminder(s).");

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
