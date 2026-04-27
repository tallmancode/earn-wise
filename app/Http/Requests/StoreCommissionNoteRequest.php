<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommissionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CommissionNote::class);
    }

    /**
     * Force company_id and branch_id to match the route-bound models so that
     * a caller cannot POST a mismatched scope through the request body.
     */
    protected function prepareForValidation(): void
    {
        $company = $this->route('company');
        $branch = $this->route('branch');

        $this->merge([
            'company_id' => $company instanceof Company ? $company->id : (int) $company,
            'branch_id' => $branch instanceof Branch ? $branch->id : (int) $branch,
        ]);
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where(fn ($q) => $q->where('branch_id', $this->branch_id)
                    ->where('company_id', $this->company_id)
                ),
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'payment_date' => ['required', 'date'],
        ];
    }
}
