<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionNoteExportService
{
    public function streamResponse(Company $company, Branch $branch, ?string $month): StreamedResponse
    {
        $query = CommissionNote::with(['employee', 'author'])
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->orderBy('payment_date');

        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            [$year, $monthNum] = explode('-', $month);
            $query->whereYear('payment_date', $year)->whereMonth('payment_date', $monthNum);
        }

        $filename = sprintf(
            'commission-notes-%s-%s%s.csv',
            Str::slug($company->name),
            Str::slug($branch->name),
            $month ? "-{$month}" : ''
        );

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Employee', 'Amount (R)', 'Payment Date',
                'Description', 'Created By', 'Created At',
            ]);

            $query->chunk(200, function ($notes) use ($handle) {
                foreach ($notes as $note) {
                    fputcsv($handle, [
                        $note->id,
                        $note->employee?->name ?? '—',
                        number_format((float) $note->amount, 2, '.', ''),
                        $note->payment_date?->toDateString(),
                        $note->description ?? '',
                        $note->author?->name ?? '—',
                        $note->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
