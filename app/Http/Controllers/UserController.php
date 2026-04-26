<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $companyId = session('selected_company_id');

        if (! $companyId) {
            return redirect()->route('dashboard');
        }

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

        return Inertia::render('Users/Index', [
            'users' => $users,
            'branches' => $branches,
            'availableRoles' => ['viewer', 'manager'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = session('selected_company_id');

        if (! $companyId) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'role' => ['required', 'string', 'in:viewer,manager'],
        ]);

        $branch = Branch::where('id', $validated['branch_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        Employee::create([
            'company_id' => $companyId,
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'name' => $validated['name'],
        ]);

        return redirect()->route('users.index');
    }
}
