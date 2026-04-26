<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanySwitchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $companyId = (int) $request->input('company_id');

        abort_unless(
            $request->user()->companies()->whereKey($companyId)->exists(),
            403,
            'You do not belong to this company.'
        );

        session(['selected_company_id' => $companyId]);

        return back();
    }
}
