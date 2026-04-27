<?php

namespace App\Notifications;

use App\Models\CommissionNote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommissionNoteAssigned extends Notification
{
    use Queueable;

    public function __construct(private readonly CommissionNote $note) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $amount = number_format((float) $this->note->amount, 2, '.', ' ');
        $branch = $this->note->branch?->name ?? 'your branch';

        return [
            'message' => "A commission of R {$amount} has been recorded for you at {$branch}.",
            'note_id' => $this->note->id,
            'amount' => $this->note->amount,
            'payment_date' => $this->note->payment_date?->toDateString(),
            'branch' => $branch,
        ];
    }
}
