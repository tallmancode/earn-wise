<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CommissionNote;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /** @return array<string, mixed> */
    public function stats(?int $companyId): array
    {
        if (! $companyId) {
            return [
                'totalThisMonth' => 0.0,
                'totalAllTime' => 0.0,
                'noteCountThisMonth' => 0,
                'branchBreakdown' => [],
                'topEmployees' => [],
                'recentNotes' => [],
            ];
        }

        $thisMonth = now();

        $totalThisMonth = (float) CommissionNote::where('company_id', $companyId)
            ->whereMonth('payment_date', $thisMonth->month)
            ->whereYear('payment_date', $thisMonth->year)
            ->sum('amount');

        $totalAllTime = (float) CommissionNote::where('company_id', $companyId)
            ->sum('amount');

        $noteCountThisMonth = CommissionNote::where('company_id', $companyId)
            ->whereMonth('payment_date', $thisMonth->month)
            ->whereYear('payment_date', $thisMonth->year)
            ->count();

        $branchBreakdown = Branch::where('company_id', $companyId)
            ->withSum(
                ['commissionNotes as total_this_month' => fn ($q) => $q
                    ->whereMonth('payment_date', $thisMonth->month)
                    ->whereYear('payment_date', $thisMonth->year)],
                'amount'
            )
            ->withSum('commissionNotes as total_all_time', 'amount')
            ->get(['id', 'name'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'total_this_month' => (float) ($b->total_this_month ?? 0),
                'total_all_time' => (float) ($b->total_all_time ?? 0),
            ]);

        $topEmployees = DB::table('employees')
            ->where('employees.company_id', $companyId)
            ->join('commission_notes', 'commission_notes.employee_id', '=', 'employees.id')
            ->select(
                'employees.id',
                'employees.name',
                DB::raw('SUM(commission_notes.amount) as total_commission'),
                DB::raw('COUNT(commission_notes.id) as note_count')
            )
            ->groupBy('employees.id', 'employees.name')
            ->orderByDesc('total_commission')
            ->limit(5)
            ->get();

        $recentNotes = CommissionNote::with(['employee', 'branch', 'author'])
            ->where('company_id', $companyId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'amount' => $n->amount,
                'payment_date' => $n->payment_date?->toDateString(),
                'description' => $n->description,
                'employee_name' => $n->employee?->name,
                'branch_name' => $n->branch?->name,
                'author_name' => $n->author?->name,
            ]);

        return [
            'totalThisMonth' => $totalThisMonth,
            'totalAllTime' => $totalAllTime,
            'noteCountThisMonth' => $noteCountThisMonth,
            'branchBreakdown' => $branchBreakdown,
            'topEmployees' => $topEmployees,
            'recentNotes' => $recentNotes,
        ];
    }
}
