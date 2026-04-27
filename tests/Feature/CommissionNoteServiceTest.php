<?php

use App\Models\CommissionNote;
use App\Models\User;
use App\Services\CommissionNoteService;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::create(['name' => 'view commission notes']);
    Permission::create(['name' => 'manage commission notes']);
});

it('allows the original author to update their own note', function () {
    $author = User::factory()->create();
    $author->givePermissionTo('view commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $author->id, 'amount' => 10000]);

    $this->actingAs($author);

    $updated = (new CommissionNoteService)->update($note, [
        'amount' => 15000,
        'payment_date' => now()->toDateString(),
    ]);

    expect($updated->amount)->toBe('15000.00');
});

it('throws when a non-author without manage permission tries to update', function () {
    $nonAuthor = User::factory()->create();
    $nonAuthor->givePermissionTo('view commission notes');

    $note = CommissionNote::factory()->create();

    $this->actingAs($nonAuthor);

    expect(fn () => (new CommissionNoteService)->update($note, [
        'amount' => 15000,
        'payment_date' => now()->toDateString(),
    ]))->toThrow(AuthorizationException::class);
});

it('allows a manager to edit someone elses note', function () {
    $author = User::factory()->create();
    $manager = User::factory()->create();
    $manager->givePermissionTo('manage commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $author->id]);

    $this->actingAs($manager);

    $service = new CommissionNoteService;
    $updated = $service->update($note, [
        'amount' => 25000,
        'payment_date' => now()->toDateString(),
    ]);

    expect($updated->amount)->toBe('25000.00');
});
