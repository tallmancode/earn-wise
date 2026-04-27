<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::create(['name' => 'view commission notes']);
    Permission::create(['name' => 'manage commission notes']);

    Role::create(['name' => 'viewer'])->givePermissionTo('view commission notes');
    Role::create(['name' => 'manager'])->givePermissionTo(['view commission notes', 'manage commission notes']);
});

it('redirects guests away from the users page', function () {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

it('forbids a viewer from accessing the users page', function () {
    $user = User::factory()->create();
    $user->assignRole('viewer');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

it('allows a manager to access the users page', function () {
    $company = Company::factory()->create();
    $manager = User::factory()->create();
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->withSession(['selected_company_id' => $company->id])
        ->get(route('users.index'))
        ->assertOk();
});

it('allows a manager to create a user and an employee record', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $manager = User::factory()->create();
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->withSession(['selected_company_id' => $company->id])
        ->post(route('users.store'), [
            'name' => 'Alice Nkosi',
            'email' => 'alice@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'branch_id' => $branch->id,
            'role' => 'viewer',
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    $this->assertDatabaseHas('employees', [
        'name' => 'Alice Nkosi',
        'branch_id' => $branch->id,
        'company_id' => $company->id,
    ]);
});

it('rejects a duplicate email on user creation', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $manager = User::factory()->create();
    $manager->assignRole('manager');
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($manager)
        ->withSession(['selected_company_id' => $company->id])
        ->post(route('users.store'), [
            'name' => 'Bob',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'branch_id' => $branch->id,
            'role' => 'viewer',
        ])
        ->assertSessionHasErrors('email');
});

it('rejects an invalid role on user creation', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $manager = User::factory()->create();
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->withSession(['selected_company_id' => $company->id])
        ->post(route('users.store'), [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'branch_id' => $branch->id,
            'role' => 'superadmin',
        ])
        ->assertSessionHasErrors('role');
});

it('rejects a branch that belongs to a different company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $branchFromB = Branch::factory()->for($companyB)->create();
    $manager = User::factory()->create();
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->withSession(['selected_company_id' => $companyA->id])
        ->post(route('users.store'), [
            'name' => 'Eve',
            'email' => 'eve@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'branch_id' => $branchFromB->id,
            'role' => 'viewer',
        ])
        ->assertSessionHasErrors('branch_id');
});
