<?php

use App\Models\Branch;
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

it('redirects guests away from notes', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();

    $this->get(route('notes.index', [$company, $branch]))
        ->assertRedirect(route('login'));
});

it('denies access to users without view permission', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();

    $this->actingAs($user)
        ->get(route('notes.index', [$company, $branch]))
        ->assertForbidden();
});

it('allows viewing notes with view permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view commission notes');

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();

    $this->actingAs($user)
        ->get(route('notes.index', [$company, $branch]))
        ->assertOk();
});

it('forbids creating a note with view-only permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view commission notes');

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    $this->actingAs($user)->post(route('notes.store', [$company, $branch]), [
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'amount' => 5000,
        'payment_date' => now()->toDateString(),
    ])->assertForbidden();
});

it('creates a note successfully with manage permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view commission notes', 'manage commission notes']);

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    $this->actingAs($user)->post(route('notes.store', [$company, $branch]), [
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'amount' => 10000,
        'payment_date' => now()->toDateString(),
    ])->assertRedirect();

    $this->assertDatabaseHas('commission_notes', [
        'employee_id' => $employee->id,
        'amount' => 10000,
    ]);
});
