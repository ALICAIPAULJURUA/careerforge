<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:technical,it,software_tools,soft,digital_media,leadership,other'],
            'proficiency' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
