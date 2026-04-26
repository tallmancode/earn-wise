<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage commission notes')
            || $this->route('note')->created_by === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'payment_date' => ['required', 'date'],
        ];
    }
}
