<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Services\CommissionNoteExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionNoteExportController extends Controller
{
    public function __construct(private CommissionNoteExportService $service) {}

    public function __invoke(Request $request, Company $company, Branch $branch): StreamedResponse
    {
        $this->authorize('view commission notes');

        return $this->service->streamResponse($company, $branch, $request->query('month'));
    }
}
