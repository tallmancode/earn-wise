<?php

use App\Models\CommissionNote;
use App\Models\User;
use App\Services\CommissionNoteService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::create(['name' => 'view commission notes']);
    Permission::create(['name' => 'manage commission notes']);
});

it('allows the original author to edit their own note', function () {
    $author = User::factory()->create();
    $author->givePermissionTo('manage commission notes');

    $note = CommissionNote::factory()->create(['created_by' => $author->id]);

    $this->actingAs($author);

    $service = new CommissionNoteService;
    $updated = $service->update($note, [
        'amount' => 15000,
        'payment_date' => now()->toDateString(),
    ]);

    expect($updated->amount)->toBe('15000.00');
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
