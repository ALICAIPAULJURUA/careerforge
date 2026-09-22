<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use App\Models\ResumeSection;
use Illuminate\Validation\ValidationException;

class AttachResumeItemAction
{
    public function __invoke(Resume $resume, string $sectionType, string $itemableType, int $itemableId, ?int $sortOrder = null)
    {
        // Find or create section for this resume+type
        $section = ResumeSection::firstOrCreate(
            [
                'resume_id' => $resume->id,
                'section_type' => $sectionType,
            ],
            [
                'is_visible' => true,
                'sort_order' => $resume->sections()->max('sort_order') !== null ? $resume->sections()->max('sort_order') + 1 : 0,
            ]
        );

        // Check that itemable belongs to same user
        $itemableClass = $itemableType;
        if (! class_exists($itemableClass)) {
            throw ValidationException::withMessages(['itemable_type' => 'Invalid itemable type.']);
        }

        $itemable = $itemableClass::find($itemableId);
        if (! $itemable) {
            throw ValidationException::withMessages(['itemable_id' => 'Item not found.']);
        }

        // Verify ownership: itemable must have user_id matching resume's user_id
        // For ExperienceAchievement, check via experience->user_id
        $ownerId = null;
        if (isset($itemable->user_id)) {
            $ownerId = $itemable->user_id;
        } elseif (method_exists($itemable, 'experience') && $itemable->experience) {
            $ownerId = $itemable->experience->user_id;
        } elseif (isset($itemable->project) && $itemable->project) {
            $ownerId = $itemable->project->user_id;
        }

        if ($ownerId !== null && $ownerId !== $resume->user_id) {
            throw ValidationException::withMessages(['itemable_id' => 'This item does not belong to you.']);
        }

        // Determine sort_order
        if ($sortOrder === null) {
            $max = $section->items()->max('sort_order');
            $sortOrder = $max !== null ? $max + 1 : 0;
        }

        // Create ResumeItem, catch duplicate unique violation
        try {
            $item = $section->items()->create([
                'itemable_type' => $itemableType,
                'itemable_id' => $itemableId,
                'sort_order' => $sortOrder,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE') || str_contains($e->getMessage(), 'unique')) {
                throw ValidationException::withMessages(['itemable_id' => 'This item is already attached to the section.']);
            }
            throw $e;
        }

        return $item;
    }
}
