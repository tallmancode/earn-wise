<?php

use App\Events\CommissionNoteCreated;
use App\Listeners\NotifyEmployeeAboutCommission;
use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\CommissionNoteAssigned;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::create(['name' => 'view commission notes']);
    Permission::create(['name' => 'manage commission notes']);
});

it('sends a database notification to the linked user when a note is created', function () {
    Notification::fake();

    $linkedUser = User::factory()->create();
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create([
        'user_id' => $linkedUser->id,
    ]);

    $note = CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'amount' => 10000,
        'payment_date' => now()->toDateString(),
    ]);

    $listener = new NotifyEmployeeAboutCommission;
    $listener->handle(new CommissionNoteCreated($note));

    Notification::assertSentTo($linkedUser, CommissionNoteAssigned::class);
});

it('does not send a notification when the employee has no linked user account', function () {
    Notification::fake();

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create([
        'user_id' => null,
    ]);

    $note = CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
    ]);

    $listener = new NotifyEmployeeAboutCommission;
    $listener->handle(new CommissionNoteCreated($note));

    Notification::assertNothingSent();
});

it('notification contains the correct commission data', function () {
    Notification::fake();

    $linkedUser = User::factory()->create();
    $company = Company::factory()->create(['name' => 'Spar']);
    $branch = Branch::factory()->for($company)->create(['name' => 'Spar Bellville']);
    $employee = Employee::factory()->for($company)->for($branch)->create([
        'user_id' => $linkedUser->id,
    ]);

    $note = CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'amount' => 20000,
        'payment_date' => '2026-04-26',
    ]);

    $listener = new NotifyEmployeeAboutCommission;
    $listener->handle(new CommissionNoteCreated($note));

    Notification::assertSentTo(
        $linkedUser,
        CommissionNoteAssigned::class,
        function (CommissionNoteAssigned $notification) use ($note) {
            $data = $notification->toDatabase($note->employee->user);

            return str_contains($data['message'], '20 000.00')
                && $data['note_id'] === $note->id
                && str_contains($data['message'], 'Spar Bellville');
        }
    );
});
