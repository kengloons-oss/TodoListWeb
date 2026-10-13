//

document.addEventListener('submit', (event) => {
    const form = event.target;
    const confirmationMessage = form instanceof HTMLFormElement ? form.dataset.confirm : null;

    if (confirmationMessage && !window.confirm(confirmationMessage)) {
        event.preventDefault();
    }
});

const skipSubtaskDialog = document.getElementById('skip-subtask-dialog');
const skipSubtaskForm = document.getElementById('skip-subtask-form');
const skipSubtaskTitle = document.getElementById('skip-subtask-title');
const skipSubtaskDecision = document.getElementById('skip-subtask-decision');

if (
    skipSubtaskDialog instanceof HTMLDialogElement
    && skipSubtaskForm instanceof HTMLFormElement
    && skipSubtaskTitle instanceof HTMLElement
    && skipSubtaskDecision instanceof HTMLInputElement
) {
    document.querySelectorAll('[data-open-skip-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            skipSubtaskForm.action = button.dataset.skipAction ?? '';
            skipSubtaskTitle.textContent = button.dataset.skipTitle ?? '';
            skipSubtaskDecision.value = 'skip';
            skipSubtaskDialog.showModal();
        });
    });

    skipSubtaskDialog.querySelector('[data-cancel-skip]')?.addEventListener('click', () => {
        skipSubtaskDialog.close();
    });

    skipSubtaskDialog.querySelectorAll('[data-skip-decision]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            skipSubtaskDecision.value = button.dataset.skipDecision ?? 'skip';
            skipSubtaskForm.requestSubmit();
        });
    });
}

const reminderDashboard = document.querySelector('[data-reminder-endpoint]');
const reminderToastContainer = document.getElementById('reminder-toast-container');
const seenReminderKeys = new Set();

if (reminderDashboard instanceof HTMLElement && reminderToastContainer instanceof HTMLElement) {
    const reminderEndpoint = reminderDashboard.dataset.reminderEndpoint;

    const hasSeenReminder = (key) => {
        try {
            if (window.localStorage.getItem(key)) {
                return true;
            }

            window.localStorage.setItem(key, '1');
            return false;
        } catch {
            if (seenReminderKeys.has(key)) {
                return true;
            }

            seenReminderKeys.add(key);
            return false;
        }
    };

    const showReminder = (reminder) => {
        const toast = document.createElement('div');
        toast.className = 'rounded-2xl border border-emerald-200 bg-white p-4 shadow-lg shadow-stone-900/10 dark:border-emerald-900 dark:bg-stone-900';

        const heading = document.createElement('p');
        heading.className = 'text-sm font-semibold text-stone-900 dark:text-stone-100';
        heading.textContent = '任务提醒';

        const title = document.createElement('p');
        title.className = 'mt-1 text-sm text-stone-600 dark:text-stone-300';
        title.textContent = reminder.title;

        const dismiss = document.createElement('button');
        dismiss.className = 'mt-3 rounded-lg px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-950';
        dismiss.type = 'button';
        dismiss.textContent = '知道了';
        dismiss.addEventListener('click', () => toast.remove());

        toast.append(heading, title, dismiss);
        reminderToastContainer.append(toast);
        window.setTimeout(() => toast.remove(), 15000);
    };

    const checkDueReminders = async () => {
        try {
            const response = await fetch(reminderEndpoint, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();

            for (const reminder of payload.reminders ?? []) {
                const reminderKey = `todolist-reminder-${reminder.task_id}-${reminder.remind_at}`;

                if (!hasSeenReminder(reminderKey)) {
                    showReminder(reminder);
                }

                await fetch(reminder.acknowledgement_url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                });
            }
        } catch {
            // A temporary network issue should not interrupt task management.
        }
    };

    checkDueReminders();
    window.setInterval(checkDueReminders, 30000);
}
