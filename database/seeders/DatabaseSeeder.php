<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        Permission::create(['name' => 'view commission notes']);
        Permission::create(['name' => 'manage commission notes']);

        // Create roles
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo('view commission notes');

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo(['view commission notes', 'manage commission notes']);

        // Create company
        $company = Company::create(['name' => 'Spar']);

        // Create two branches
        $branchA = Branch::create(['company_id' => $company->id, 'name' => 'Spar Bellville']);
        $branchB = Branch::create(['company_id' => $company->id, 'name' => 'Spar Gardens']);

        // Create two employees in different branches
        $emp1 = Employee::create(['company_id' => $company->id, 'branch_id' => $branchA->id, 'name' => 'Alice Nkosi']);
        $emp2 = Employee::create(['company_id' => $company->id, 'branch_id' => $branchB->id, 'name' => 'Bob Dlamini']);

        // Create users
        $managerUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $managerUser->assignRole('manager');

        $viewerUser = User::create([
            'name' => 'View Only',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password'),
        ]);
        $viewerUser->assignRole('viewer');

        // Seed the two commission notes as per the exercise
        CommissionNote::create([
            'company_id' => $company->id,
            'branch_id' => $branchA->id,
            'employee_id' => $emp1->id,
            'created_by' => $managerUser->id,
            'amount' => 10000.00,
            'description' => 'Commission payment - Alice',
            'payment_date' => now()->toDateString(),
        ]);

        CommissionNote::create([
            'company_id' => $company->id,
            'branch_id' => $branchB->id,
            'employee_id' => $emp2->id,
            'created_by' => $managerUser->id,
            'amount' => 20000.00,
            'description' => 'Commission payment - Bob',
            'payment_date' => now()->toDateString(),
        ]);
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
