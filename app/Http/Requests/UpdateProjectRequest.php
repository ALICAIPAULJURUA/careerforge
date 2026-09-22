<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'technologies' => ['sometimes', 'array'],
            'technologies.*.id' => ['sometimes', 'integer', 'exists:project_technologies,id'],
            'technologies.*._delete' => ['sometimes', 'boolean'],
            'technologies.*.name' => ['exclude_if:technologies.*._delete,true', 'exclude_if:technologies.*._delete,1', 'nullable', 'string', 'max:100'],
            'technologies.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
