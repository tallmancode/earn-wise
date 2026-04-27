<?php

namespace App\Listeners;

use App\Events\CommissionNoteCreated;
use App\Notifications\CommissionNoteAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyEmployeeAboutCommission implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 30;

    public function handle(CommissionNoteCreated $event): void
    {
        $note = $event->note->loadMissing('employee.user', 'branch');

        if (! $note->employee || ! $note->employee->user_id) {
            return;
        }

        $note->employee->user->notify(new CommissionNoteAssigned($note));
    }
}
