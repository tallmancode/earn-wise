<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(): Response|RedirectResponse
    {
        $companyId = session('selected_company_id');

        if (! $companyId) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Users/Index', [
            ...$this->service->listForCompany((int) $companyId),
            'availableRoles' => ['viewer', 'manager'],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $companyId = session('selected_company_id');

        if (! $companyId) {
            return redirect()->route('dashboard');
        }

        $this->service->create((int) $companyId, $request->validated());

        return redirect()->route('users.index');
    }
}
