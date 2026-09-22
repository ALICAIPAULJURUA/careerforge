<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResumeItemRequest extends FormRequest
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
            'section_type' => ['required', 'string', 'in:summary,experience,education,skills,projects,certifications,awards,leadership,languages,references'],
            'itemable_type' => ['required', 'string', 'in:App\Models\Experience,App\Models\ExperienceAchievement,App\Models\Education,App\Models\Skill,App\Models\Project,App\Models\Certification,App\Models\Award,App\Models\Leadership,App\Models\Language,App\Models\Reference'],
            'itemable_id' => ['required', 'integer'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
