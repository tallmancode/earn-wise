<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $selectedCompanyId = session('selected_company_id');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'companies' => $user ? $user->companies()->get(['id', 'name']) : [],
                'selectedCompanyId' => $selectedCompanyId,
                'branches' => $user && $selectedCompanyId
                    ? Branch::where('company_id', $selectedCompanyId)->get(['id', 'name'])
                    : [],
                'roles' => $user ? $user->getRoleNames() : [],
                'unreadNotificationsCount' => $user ? $user->unreadNotifications()->count() : 0,
            ],
        ];
    }
}
