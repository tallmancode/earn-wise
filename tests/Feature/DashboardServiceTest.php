<?php

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Services\DashboardService;

it('returns all-zero stats when company id is null', function () {
    $stats = (new DashboardService)->stats(null);

    expect($stats['totalThisMonth'])->toBe(0.0)
        ->and($stats['totalAllTime'])->toBe(0.0)
        ->and($stats['noteCountThisMonth'])->toBe(0)
        ->and($stats['branchBreakdown'])->toBeEmpty()
        ->and($stats['topEmployees'])->toBeEmpty()
        ->and($stats['recentNotes'])->toBeEmpty();
});

it('returns correct totals for a company with notes this month', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $author = User::factory()->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $author->id,
        'amount' => 10000,
        'payment_date' => now()->toDateString(),
    ]);

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $author->id,
        'amount' => 5000,
        'payment_date' => now()->toDateString(),
    ]);

    $stats = (new DashboardService)->stats($company->id);

    expect($stats['totalAllTime'])->toBe(15000.0)
        ->and($stats['totalThisMonth'])->toBe(15000.0)
        ->and($stats['noteCountThisMonth'])->toBe(2)
        ->and($stats['recentNotes'])->toHaveCount(2);
});

it('does not count notes from a previous month in totalThisMonth', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->for($company)->create();
    $author = User::factory()->create();
    $employee = Employee::factory()->for($company)->for($branch)->create();

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $author->id,
        'amount' => 8000,
        'payment_date' => now()->subMonth()->toDateString(),
    ]);

    CommissionNote::factory()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'employee_id' => $employee->id,
        'created_by' => $author->id,
        'amount' => 3000,
        'payment_date' => now()->toDateString(),
    ]);

    $stats = (new DashboardService)->stats($company->id);

    expect($stats['totalAllTime'])->toBe(11000.0)
        ->and($stats['totalThisMonth'])->toBe(3000.0)
        ->and($stats['noteCountThisMonth'])->toBe(1);
});

it('does not include notes from other companies', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $branchA = Branch::factory()->for($companyA)->create();
    $author = User::factory()->create();
    $employeeA = Employee::factory()->for($companyA)->for($branchA)->create();

    $branchB = Branch::factory()->for($companyB)->create();
    $employeeB = Employee::factory()->for($companyB)->for($branchB)->create();

    CommissionNote::factory()->create([
        'company_id' => $companyA->id,
        'branch_id' => $branchA->id,
        'employee_id' => $employeeA->id,
        'created_by' => $author->id,
        'amount' => 5000,
        'payment_date' => now()->toDateString(),
    ]);

    CommissionNote::factory()->create([
        'company_id' => $companyB->id,
        'branch_id' => $branchB->id,
        'employee_id' => $employeeB->id,
        'created_by' => $author->id,
        'amount' => 99999,
        'payment_date' => now()->toDateString(),
    ]);

    $stats = (new DashboardService)->stats($companyA->id);

    expect($stats['totalAllTime'])->toBe(5000.0)
        ->and($stats['totalThisMonth'])->toBe(5000.0);
});
