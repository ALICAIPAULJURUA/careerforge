<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResumeRequest extends FormRequest
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
            'target_role' => ['nullable', 'string', 'max:255'],
            'template_id' => ['required', 'exists:templates,id'],
            'theme_id' => ['required', 'exists:themes,id'],
            'font_override' => ['nullable', 'string', 'max:100'],
            'photo_enabled' => ['sometimes', 'boolean'],
            'photo_style' => ['sometimes', 'string', 'in:none,circle,square,rounded'],
            'layout' => ['sometimes', 'string', 'in:one_column,two_column,sidebar'],
            'ats_mode' => ['sometimes', 'boolean'],
            'page_size' => ['sometimes', 'string', 'in:a4,letter'],
            'slug' => ['nullable', 'string', 'max:255'],
        ];
    }
}
