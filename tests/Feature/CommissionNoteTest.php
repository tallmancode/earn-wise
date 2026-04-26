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

it('forbids updating a note the view-only user did not author', function () {
    $author = User::factory()->create();
    $author->givePermissionTo(['view commission notes', 'manage commission notes']);

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $author->id]);

    $this->actingAs($viewer)->patch(route('notes.update', $note), [
        'amount' => 99999,
        'payment_date' => now()->toDateString(),
    ])->assertForbidden();
});

it('allows a view-only user to update a note they authored', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $viewer->id]);

    $this->actingAs($viewer)->patch(route('notes.update', $note), [
        'amount' => 12000,
        'payment_date' => now()->toDateString(),
    ])->assertRedirect();

    $this->assertDatabaseHas('commission_notes', [
        'id' => $note->id,
        'amount' => 12000,
    ]);
});

it('forbids deleting a note the view-only user did not author', function () {
    $author = User::factory()->create();
    $author->givePermissionTo(['view commission notes', 'manage commission notes']);

    $viewer = User::factory()->create();
    $viewer->givePermissionTo('view commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $author->id]);

    $this->actingAs($viewer)->delete(route('notes.destroy', $note))
        ->assertForbidden();
});

it('allows the original author to delete their own note', function () {
    $author = User::factory()->create();
    $author->givePermissionTo('view commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $author->id]);

    $this->actingAs($author)->delete(route('notes.destroy', $note))
        ->assertRedirect();

    $this->assertSoftDeleted('commission_notes', ['id' => $note->id]);
});

it('does not return notes from other branches', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view commission notes');

    $company = Company::factory()->create();
    $branchA = Branch::factory()->for($company)->create();
    $branchB = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branchA)->create();

    $note = CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branchA->id,
        'employee_id' => $employee->id,
    ]);

    $this->actingAs($user)
        ->get(route('notes.index', [$company, $branchB]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('CommissionNotes/Index')
            ->where('notes.data', fn ($notes) => collect($notes)->pluck('id')->doesntContain($note->id)
            )
        );
});

it('rejects negative commission amounts', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view commission notes', 'manage commission notes']);

    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    $this->actingAs($user)->post(route('notes.store', [$company, $branch]), [
        'employee_id' => $employee->id,
        'amount' => -100,
        'payment_date' => now()->toDateString(),
    ])->assertSessionHasErrors('amount');
});

it('returns 404 when patching a soft-deleted note', function () {
    $author = User::factory()->create();
    $author->givePermissionTo(['view commission notes', 'manage commission notes']);

    $note = CommissionNote::factory()->create(['created_by' => $author->id]);
    $note->delete();

    $this->actingAs($author)->patch(route('notes.update', $note->id), [
        'amount' => 500,
        'payment_date' => now()->toDateString(),
    ])->assertNotFound();
});
