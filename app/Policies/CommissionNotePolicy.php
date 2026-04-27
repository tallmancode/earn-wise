<?php

namespace App\Policies;

use App\Models\CommissionNote;
use App\Models\User;

class CommissionNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view commission notes');
    }

    public function create(User $user): bool
    {
        return $user->can('manage commission notes');
    }

    public function update(User $user, CommissionNote $note): bool
    {
        return $user->can('manage commission notes');
    }

    public function delete(User $user, CommissionNote $note): bool
    {
        return $user->can('manage commission notes');
    }

    public function restore(User $user, CommissionNote $note): bool
    {
        return $user->can('manage commission notes');
    }
}
