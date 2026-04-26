<?php

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::create(['name' => 'view commission notes']);
    Permission::create(['name' => 'manage commission notes']);
});

it('redirects guests away from the export endpoint', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();

    $this->get(route('notes.export', [$company, $branch]))
        ->assertRedirect(route('login'));
});

it('allows a view-only user to export notes as CSV', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view commission notes');

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $user->id,
        'amount' => 12500,
        'payment_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('notes.export', [$company, $branch]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('Employee')
        ->toContain('Amount (R)')
        ->toContain('12500.00')
        ->toContain($employee->name);
});

it('denies export to users without view permission', function () {
    $user = User::factory()->create();

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();

    $this->actingAs($user)
        ->get(route('notes.export', [$company, $branch]))
        ->assertForbidden();
});

it('filters export by month when month query param is provided', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view commission notes');

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $user->id,
        'amount' => 9999,
        'payment_date' => '2026-01-15',
    ]);

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $user->id,
        'amount' => 5555,
        'payment_date' => '2026-03-10',
    ]);

    $response = $this->actingAs($user)
        ->get(route('notes.export', [$company, $branch]).'?month=2026-01');

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('9999.00')
        ->not->toContain('5555.00');
});
