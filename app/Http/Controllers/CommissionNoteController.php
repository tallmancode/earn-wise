<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommissionNoteRequest;
use App\Http\Requests\UpdateCommissionNoteRequest;
use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\User;
use App\Services\CommissionNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CommissionNoteController extends Controller
{
    public function __construct(private CommissionNoteService $service) {}

    public function index(Company $company, Branch $branch): Response
    {
        $this->authorize('view commission notes');

        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('CommissionNotes/Index', [
            'company' => $company,
            'branch' => $branch,
            'notes' => $this->service->list($company->id, $branch->id),
            'employees' => $branch->employees,
            'canManage' => $user->can('manage commission notes'),
        ]);
    }

    public function store(StoreCommissionNoteRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', 'Commission note created.');
    }

    public function update(UpdateCommissionNoteRequest $request, CommissionNote $note): RedirectResponse
    {
        $this->service->update($note, $request->validated());

        return back()->with('success', 'Commission note updated.');
    }

    public function destroy(CommissionNote $note): RedirectResponse
    {
        $this->authorize('manage commission notes');

        $this->service->delete($note);

        return back()->with('success', 'Commission note deleted.');
    }
}
