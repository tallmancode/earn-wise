<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already protected by the role:manager middleware.
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = session('selected_company_id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where('company_id', $companyId),
            ],
            'role' => ['required', 'string', 'in:viewer,manager'],
        ];
    }
}
