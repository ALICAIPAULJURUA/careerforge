<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_unless:is_current,true'],
            'is_current' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'achievements' => ['sometimes', 'array'],
            'achievements.*.content' => ['nullable', 'string'],
            'achievements.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_current')) {
            $this->merge([
                'is_current' => filter_var($this->input('is_current'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
