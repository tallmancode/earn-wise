<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /** @return array{users: Collection, branches: Collection} */
    public function listForCompany(int $companyId): array
    {
        $branches = Branch::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'company_id']);

        $users = User::whereIn(
            'id',
            Employee::where('company_id', $companyId)->select('user_id')
        )
            ->with(['employees' => fn ($q) => $q->where('company_id', $companyId)->with('branch:id,name')])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'branches' => $user->employees->map(fn ($e) => [
                    'id' => $e->branch?->id,
                    'name' => $e->branch?->name,
                ]),
            ]);

        return compact('users', 'branches');
    }

    public function create(int $companyId, array $validated): User
    {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        Employee::create([
            'company_id' => $companyId,
            'branch_id' => $validated['branch_id'],
            'user_id' => $user->id,
            'name' => $validated['name'],
        ]);

        return $user;
    }
}
