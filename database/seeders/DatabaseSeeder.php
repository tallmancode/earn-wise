<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions & roles
        Permission::create(['name' => 'view commission notes']);
        Permission::create(['name' => 'manage commission notes']);

        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo('view commission notes');

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo(['view commission notes', 'manage commission notes']);

        // ── Spar (primary company with known fixed credentials) ──────────────
        $spar = Company::create(['name' => 'Spar']);

        $bellville = Branch::create(['company_id' => $spar->id, 'name' => 'Spar Bellville']);
        $gardens = Branch::create(['company_id' => $spar->id, 'name' => 'Spar Gardens']);

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

        $emp1 = Employee::create(['company_id' => $spar->id, 'branch_id' => $bellville->id, 'user_id' => $managerUser->id, 'name' => 'Alice Nkosi']);
        $emp2 = Employee::create(['company_id' => $spar->id, 'branch_id' => $gardens->id,   'user_id' => $managerUser->id, 'name' => 'Bob Dlamini']);
        Employee::create(['company_id' => $spar->id, 'branch_id' => $bellville->id, 'user_id' => $viewerUser->id, 'name' => 'View Only']);

        CommissionNote::create([
            'company_id' => $spar->id,
            'branch_id' => $bellville->id,
            'employee_id' => $emp1->id,
            'created_by' => $managerUser->id,
            'amount' => 10000.00,
            'description' => 'Commission payment - Alice',
            'payment_date' => now()->toDateString(),
        ]);

        CommissionNote::create([
            'company_id' => $spar->id,
            'branch_id' => $gardens->id,
            'employee_id' => $emp2->id,
            'created_by' => $managerUser->id,
            'amount' => 20000.00,
            'description' => 'Commission payment - Bob',
            'payment_date' => now()->toDateString(),
        ]);

        $this->command->info('Spar | manager@example.com | viewer@example.com | password: password');

        // ── 4 additional factory companies ───────────────────────────────────
        Company::factory()->count(4)->create()->each(function (Company $company) {
            $slug = Str::slug($company->name);

            $mgr = User::create([
                'name' => 'Manager - '.$company->name,
                'email' => 'manager@'.$slug.'.test',
                'password' => Hash::make('password'),
            ]);
            $mgr->assignRole('manager');

            $vwr = User::create([
                'name' => 'Viewer - '.$company->name,
                'email' => 'viewer@'.$slug.'.test',
                'password' => Hash::make('password'),
            ]);
            $vwr->assignRole('viewer');

            $branches = Branch::factory()->count(rand(3, 6))->create(['company_id' => $company->id]);

            Employee::create([
                'company_id' => $company->id,
                'branch_id' => $branches->first()->id,
                'user_id' => $mgr->id,
                'name' => $mgr->name,
            ]);

            Employee::create([
                'company_id' => $company->id,
                'branch_id' => $branches->first()->id,
                'user_id' => $vwr->id,
                'name' => $vwr->name,
            ]);

            $branches->each(function (Branch $branch) use ($company) {
                Employee::factory()->count(rand(5, 10))->create([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                ]);
            });

            $this->command->info($company->name.' | manager@'.$slug.'.test | viewer@'.$slug.'.test | password: password');
        });
    }
}
