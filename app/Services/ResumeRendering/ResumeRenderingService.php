<?php

namespace App\Services\ResumeRendering;

use App\DataTransferObjects\RenderableResume;
use App\Models\Resume;
use Illuminate\Support\Str;

class ResumeRenderingService
{
    public function render(Resume $resume): RenderableResume
    {
        $resume->loadMissing(['template', 'theme', 'sections.items.itemable']);

        $user = $resume->user;
        $profile = $user?->profile;

        // Personal
        $personal = [
            'full_name' => $profile?->full_name ?: $user?->name,
            'professional_title' => $profile?->professional_title,
            'email' => $user?->email,
            'phone' => $profile?->phone,
            'location' => $profile?->location,
            'website_url' => $profile?->website_url,
            'linkedin_url' => $profile?->linkedin_url,
            'github_url' => $profile?->github_url,
            'photo_url' => $this->resolvePhotoUrl($resume, $profile),
            'photo_path' => $profile?->photo_path,
        ];

        // Meta
        $meta = [
            'name' => $resume->name,
            'target_role' => $resume->target_role,
            'template_key' => $resume->template?->key,
            'template_name' => $resume->template?->name,
            'theme' => $resume->theme ? [
                'name' => $resume->theme->name,
                'primary_color' => $resume->theme->primary_color,
                'secondary_color' => $resume->theme->secondary_color,
                'text_color' => $resume->theme->text_color,
                'background_color' => $resume->theme->background_color,
                'sidebar_color' => $resume->theme->sidebar_color,
                'font_family' => $resume->theme->font_family,
                'heading_size' => $resume->theme->heading_size,
                'body_size' => $resume->theme->body_size,
            ] : null,
            'photo_enabled' => (bool) $resume->photo_enabled,
            'photo_style' => $resume->photo_style,
            'layout' => $resume->layout,
            'ats_mode' => (bool) $resume->ats_mode,
            'page_size' => $resume->page_size,
            'slug' => $resume->slug,
        ];

        // If theme says supports_photo false, photo_url should be null regardless
        if ($resume->template && !$resume->template->supports_photo) {
            $personal['photo_url'] = null;
            $meta['photo_enabled'] = false;
        } elseif (!$resume->photo_enabled) {
            $personal['photo_url'] = null;
        }

        // Sections
        $sections = [];
        $orderedSections = $resume->sections->where('is_visible', true)->sortBy('sort_order')->values();

        foreach ($orderedSections as $section) {
            $items = $section->items->sortBy('sort_order')->values();

            // Eager loaded itemable, filter out missing (if master deleted, itemable may be null)
            $items = $items->filter(fn($ri) => $ri->itemable !== null);

            $renderedItems = $this->renderSectionItems($section->section_type, $items, $resume);

            $sections[] = [
                'type' => $section->section_type,
                'title' => $this->sectionTitle($section->section_type),
                'is_visible' => (bool) $section->is_visible,
                'sort_order' => $section->sort_order,
                'items' => $renderedItems,
            ];
        }

        return new RenderableResume($meta, $personal, $sections);
    }

    protected function resolvePhotoUrl(Resume $resume, $profile): ?string
    {
        if (!$profile || !$profile->photo_path) {
            return null;
        }
        if (!$resume->photo_enabled) {
            return null;
        }
        // For preview, use the private photo route if available, otherwise null
        // Generate URL to career-profile photo route would require auth; for rendered resume we return a placeholder route
        // The template can decide to show <img src="{{ route('career-profile.photo') }}"> but that needs auth
        // For now, return a signed URL placeholder: we use Storage::disk('private') temporaryUrl if S3 else route
        // To keep service pure, return a string that template can use as src: we return route name placeholder
        // Instead, return the storage path; template will handle via Storage::disk('private')->temporaryUrl or similar
        // For Phase 6 preview (authenticated), we can use route('career-profile.photo') as photo_url
        try {
            if (request() && auth()->check() && auth()->id() === $resume->user_id) {
                return route('career-profile.photo');
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return null;
    }

    protected function sectionTitle(string $type): string
    {
        return match ($type) {
            'experience' => 'Experience',
            'education' => 'Education',
            'skills' => 'Skills',
            'projects' => 'Projects',
            'summary' => 'Summary',
            'certifications' => 'Certifications',
            'awards' => 'Awards',
            'leadership' => 'Leadership',
            'languages' => 'Languages',
            'references' => 'References',
            default => Str::title(str_replace('_', ' ', $type)),
        };
    }

    protected function renderSectionItems(string $sectionType, $items, Resume $resume): array
    {
        // Handle experience specially: group achievements under parent experience
        if ($sectionType === 'experience') {
            return $this->renderExperienceItems($items);
        }

        if ($sectionType === 'skills') {
            return $this->renderSkillItems($items);
        }

        if ($sectionType === 'projects') {
            return $this->renderProjectItems($items);
        }

        if ($sectionType === 'education') {
            return $this->renderEducationItems($items);
        }

        // Generic fallback: just map itemable attributes
        $result = [];
        foreach ($items as $ri) {
            $model = $ri->itemable;
            $data = $model->toArray();
            // Ensure resume sort_order is available
            $data['_resume_sort_order'] = $ri->sort_order;
            $data['_resume_item_id'] = $ri->id;
            $result[] = $data;
        }
        return $result;
    }

    protected function renderExperienceItems($items): array
    {
        // Separate experiences and achievements
        $experiences = [];
        $achievementsByExperience = [];

        foreach ($items as $ri) {
            $model = $ri->itemable;
            if ($model instanceof \App\Models\Experience) {
                $experiences[] = ['ri' => $ri, 'model' => $model];
            } elseif ($model instanceof \App\Models\ExperienceAchievement) {
                $expId = $model->experience_id;
                $achievementsByExperience[$expId][] = ['ri' => $ri, 'model' => $model];
            }
        }

        // Sort experiences by resume sort_order ( уже sorted via $items sort)
        // Experiences are already in order as they appeared in $items filtered; but we should preserve that order
        // Instead, we collected in order of appearance, so keep that.

        $result = [];
        foreach ($experiences as $entry) {
            $ri = $entry['ri'];
            $exp = $entry['model'];
            $expData = $exp->toArray();
            $expData['_resume_sort_order'] = $ri->sort_order;
            $expData['_resume_item_id'] = $ri->id;

            // Attach achievements grouped under this experience, ordered by resume sort_order
            $achs = $achievementsByExperience[$exp->id] ?? [];
            // Sort achievements by resume sort_order
            usort($achs, fn($a, $b) => $a['ri']->sort_order <=> $b['ri']->sort_order);
            $expData['achievements'] = array_map(function ($achEntry) {
                $ach = $achEntry['model'];
                $ri = $achEntry['ri'];
                $data = $ach->toArray();
                $data['_resume_sort_order'] = $ri->sort_order;
                $data['_resume_item_id'] = $ri->id;
                return $data;
            }, $achs);

            $result[] = $expData;
        }

        // If there are achievements whose parent experience is not selected, they are orphaned — we skip them
        // (Alternative would be to render them alone, but spec says grouping under parent)

        return $result;
    }

    protected function renderSkillItems($items): array
    {
        $result = [];
        foreach ($items as $ri) {
            $skill = $ri->itemable;
            $data = $skill->toArray();
            $data['_resume_sort_order'] = $ri->sort_order;
            $data['_resume_item_id'] = $ri->id;
            $result[] = $data;
        }
        // Keep resume order, but also could group by category if needed — template will handle
        return $result;
    }

    protected function renderProjectItems($items): array
    {
        $result = [];
        foreach ($items as $ri) {
            $proj = $ri->itemable;
            // Eager load technologies if not already
            if (!$proj->relationLoaded('technologies')) {
                $proj->load('technologies');
            }
            $data = $proj->toArray();
            $data['technologies'] = $proj->technologies->sortBy('sort_order')->values()->toArray();
            $data['_resume_sort_order'] = $ri->sort_order;
            $data['_resume_item_id'] = $ri->id;
            $result[] = $data;
        }
        return $result;
    }

    protected function renderEducationItems($items): array
    {
        $result = [];
        foreach ($items as $ri) {
            $edu = $ri->itemable;
            $data = $edu->toArray();
            $data['_resume_sort_order'] = $ri->sort_order;
            $data['_resume_item_id'] = $ri->id;
            $result[] = $data;
        }
        return $result;
    }
}
