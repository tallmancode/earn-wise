<?php

namespace App\Services;

use App\Events\CommissionNoteCreated;
use App\Models\CommissionNote;
use App\Models\CommissionNoteAudit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CommissionNoteService
{
    public function list(int $companyId, int $branchId): Collection
    {
        return CommissionNote::with(['employee', 'author', 'audits.actor'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->latest()
            ->get();
    }

    public function create(array $validated): CommissionNote
    {
        $note = CommissionNote::create([
            'company_id' => $validated['company_id'],
            'branch_id' => $validated['branch_id'],
            'employee_id' => $validated['employee_id'],
            'created_by' => Auth::id(),
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'payment_date' => $validated['payment_date'],
        ]);

        $this->recordAudit('created', $note->id, null, $this->auditableValues($note));

        event(new CommissionNoteCreated($note));

        return $note;
    }

    public function update(CommissionNote $note, array $validated): CommissionNote
    {
        /** @var User $user */
        $user = Auth::user();

        if ($note->created_by !== $user->id && ! $user->can('manage commission notes')) {
            throw new AuthorizationException('You are not authorised to edit this note.');
        }

        $oldValues = $this->auditableValues($note);

        $note->update([
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? $note->description,
            'payment_date' => $validated['payment_date'],
        ]);

        $note->refresh();

        $this->recordAudit('updated', $note->id, $oldValues, $this->auditableValues($note));

        return $note;
    }

    public function delete(CommissionNote $note): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($note->created_by !== $user->id && ! $user->can('manage commission notes')) {
            throw new AuthorizationException('You are not authorised to delete this note.');
        }

        $this->recordAudit('deleted', $note->id, $this->auditableValues($note), null);

        $note->delete();
    }

    /** @return array<string, mixed> */
    private function auditableValues(CommissionNote $note): array
    {
        return [
            'amount' => $note->amount,
            'description' => $note->description,
            'payment_date' => $note->payment_date?->toDateString(),
            'employee_id' => $note->employee_id,
        ];
    }

    /** @param array<string, mixed>|null $oldValues */
    /** @param array<string, mixed>|null $newValues */
    private function recordAudit(string $event, int $noteId, ?array $oldValues, ?array $newValues): void
    {
        CommissionNoteAudit::create([
            'commission_note_id' => $noteId,
            'user_id' => Auth::id(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
