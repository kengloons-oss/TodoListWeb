<?php

namespace App\Mail;

use App\Models\TaskReminder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TaskReminderMail extends Mailable
{
    public function __construct(public TaskReminder $reminder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '任务提醒：'.$this->reminder->task->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.task-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
