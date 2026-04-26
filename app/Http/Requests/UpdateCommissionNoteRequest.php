<?php

namespace App\Http\Requests;

use App\Models\CommissionNote;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CommissionNote $note */
        $note = $this->route('note');

        return $this->user()->can('update', $note);
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
