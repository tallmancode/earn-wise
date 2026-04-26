<?php

namespace App\Services;

use App\Models\CommissionNote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CommissionNoteService
{
    public function list(int $companyId, int $branchId): Collection
    {
        return CommissionNote::with(['employee', 'author'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->latest()
            ->get();
    }

    public function create(array $validated): CommissionNote
    {
        return CommissionNote::create([
            'company_id' => $validated['company_id'],
            'branch_id' => $validated['branch_id'],
            'employee_id' => $validated['employee_id'],
            'created_by' => Auth::id(),
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'payment_date' => $validated['payment_date'],
        ]);
    }

    public function update(CommissionNote $note, array $validated): CommissionNote
    {
        /** @var User $user */
        $user = Auth::user();

        if ($note->created_by !== $user->id && ! $user->can('manage commission notes')) {
            throw new AuthorizationException('You are not authorised to edit this note.');
        }

        $note->update([
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? $note->description,
            'payment_date' => $validated['payment_date'],
        ]);

        return $note->fresh();
    }

    public function delete(CommissionNote $note): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($note->created_by !== $user->id && ! $user->can('manage commission notes')) {
            throw new AuthorizationException('You are not authorised to delete this note.');
        }

        $note->delete();
    }
}
