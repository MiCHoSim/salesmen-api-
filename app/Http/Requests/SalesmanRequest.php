<?php

namespace App\Http\Requests;

use App\Services\CodelistService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesmanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'titles_before' => ['nullable', 'array', 'max:10'],
            'titles_before.*' => ['string', 'min:2', 'max:10', Rule::in(CodelistService::TITLES_BEFORE)],
            'titles_after' => ['nullable', 'array', 'max:10'],
            'titles_after.*' => ['string', 'min:2', 'max:10', Rule::in(CodelistService::TITLES_AFTER)],
            'prosight_id' => ['required', 'string', 'size:5', 'regex:/^\d{5}$/'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', Rule::in(CodelistService::genderCodes())],
            'marital_status' => ['nullable', 'string', Rule::in(CodelistService::maritalStatusCodes())],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $salesmanId = $this->route('salesman');

            $rules['prosight_id'][] = Rule::unique('salesmen')->ignore($salesmanId);
            $rules['email'][] = Rule::unique('salesmen')->ignore($salesmanId);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titles_before.*.in' => 'The selected title before is invalid.',
            'titles_after.*.in' => 'The selected title after is invalid.',
            'prosight_id.regex' => 'The prosight id must be exactly 5 digits.',
        ];
    }
}
