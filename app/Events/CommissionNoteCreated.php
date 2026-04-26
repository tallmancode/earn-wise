<?php

namespace App\Events;

use App\Models\CommissionNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommissionNoteCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CommissionNote $note) {}
}
