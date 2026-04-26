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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CommissionNoteController extends Controller
{
    public function __construct(private CommissionNoteService $service) {}

    public function index(Company $company, Branch $branch, Request $request): Response
    {
        $this->authorize('viewAny', CommissionNote::class);

        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('CommissionNotes/Index', [
            'company' => $company,
            'branch' => $branch,
            'notes' => $this->service->list($company->id, $branch->id, $request->query('search')),
            'employees' => $branch->employees,
            'canManage' => $user->can('manage commission notes'),
            'filters' => ['search' => $request->query('search', '')],
        ]);
    }

    public function store(StoreCommissionNoteRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', 'Commission note created.');
    }

    public function update(UpdateCommissionNoteRequest $request, CommissionNote $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $this->service->update($note, $request->validated());

        return back()->with('success', 'Commission note updated.');
    }

    public function destroy(CommissionNote $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $this->service->delete($note);

        return back()->with('success', 'Commission note deleted.');
    }

    public function restore(CommissionNote $note): RedirectResponse
    {
        $this->authorize('restore', $note);

        $this->service->restore($note);

        return back()->with('success', 'Commission note restored.');
    }
}
