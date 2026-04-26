<?php

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\CommissionNoteAudit;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Services\CommissionNoteService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::create(['name' => 'view commission notes']);
    Permission::create(['name' => 'manage commission notes']);
});

it('records a created audit entry when a note is created', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view commission notes', 'manage commission notes']);

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    $this->actingAs($user);

    $service = new CommissionNoteService;
    $note = $service->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'amount' => 10000,
        'payment_date' => now()->toDateString(),
    ]);

    $this->assertDatabaseHas('commission_note_audits', [
        'commission_note_id' => $note->id,
        'user_id' => $user->id,
        'event' => 'created',
    ]);

    $audit = CommissionNoteAudit::where('commission_note_id', $note->id)->first();
    expect($audit->old_values)->toBeNull()
        ->and($audit->new_values)->not->toBeNull()
        ->and($audit->new_values['amount'])->toBe('10000.00');
});

it('records an updated audit entry with old and new values when a note is edited', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view commission notes', 'manage commission notes']);

    $note = CommissionNote::factory()->create([
        'created_by' => $user->id,
        'amount' => 10000,
        'payment_date' => '2026-04-01',
    ]);

    $this->actingAs($user);

    $service = new CommissionNoteService;
    $service->update($note, [
        'amount' => 15000,
        'payment_date' => '2026-04-15',
    ]);

    $audit = CommissionNoteAudit::where('commission_note_id', $note->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->old_values['amount'])->toBe('10000.00')
        ->and($audit->new_values['amount'])->toBe('15000.00')
        ->and($audit->old_values['payment_date'])->toBe('2026-04-01')
        ->and($audit->new_values['payment_date'])->toBe('2026-04-15');
});

it('records a deleted audit entry when a note is deleted', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view commission notes', 'manage commission notes']);

    $note = CommissionNote::factory()->create([
        'created_by' => $user->id,
        'amount' => 5000,
    ]);

    $noteId = $note->id;

    $this->actingAs($user);

    $service = new CommissionNoteService;
    $service->delete($note);

    $this->assertDatabaseMissing('commission_notes', ['id' => $noteId]);

    $audit = CommissionNoteAudit::where('commission_note_id', $noteId)
        ->where('event', 'deleted')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->old_values['amount'])->toBe('5000.00')
        ->and($audit->new_values)->toBeNull();
});
