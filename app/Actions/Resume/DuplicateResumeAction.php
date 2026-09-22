<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuplicateResumeAction
{
    public function __invoke(Resume $resume): Resume
    {
        return DB::transaction(function () use ($resume) {
            $resume->loadMissing(['sections.items', 'template', 'theme']);

            // Copy resume attributes, generate new name
            $originalName = $resume->name;
            $newName = 'Copy of ' . $originalName;
            // Ensure unique if already copied: if name already starts with Copy, keep prefixing is okay for MVP

            $data = [
                'name' => $newName,
                'target_role' => $resume->target_role,
                'template_id' => $resume->template_id,
                'theme_id' => $resume->theme_id,
                'font_override' => $resume->font_override,
                'photo_enabled' => $resume->photo_enabled,
                'photo_style' => $resume->photo_style,
                'layout' => $resume->layout,
                'ats_mode' => $resume->ats_mode,
                'page_size' => $resume->page_size,
                'slug' => null, // do not copy slug, keep null for new resume
            ];

            $copy = $resume->user->resumes()->create($data);

            // Deep copy sections and items preserving order
            foreach ($resume->sections as $section) {
                $newSection = $copy->sections()->create([
                    'section_type' => $section->section_type,
                    'is_visible' => $section->is_visible,
                    'sort_order' => $section->sort_order,
                ]);

                foreach ($section->items as $item) {
                    $newSection->items()->create([
                        'itemable_type' => $item->itemable_type,
                        'itemable_id' => $item->itemable_id,
                        'sort_order' => $item->sort_order,
                    ]);
                }
            }

            // Duplicate overrides if they exist (Phase 12 not yet built, but handle gracefully)
            if (class_exists(\App\Models\ResumeOverride::class) && method_exists($resume, 'overrides')) {
                try {
                    $overrides = $resume->overrides()->get();
                    foreach ($overrides as $override) {
                        $copy->overrides()->create([
                            'overridable_type' => $override->overridable_type,
                            'overridable_id' => $override->overridable_id,
                            'field_name' => $override->field_name,
                            'override_value' => $override->override_value,
                        ]);
                    }
                } catch (\Throwable $e) {
                    // If overrides table not migrated yet, ignore
                }
            }

            // Also handle generic resume_overrides via direct table check if model not exists
            if (!class_exists(\App\Models\ResumeOverride::class)) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('resume_overrides')) {
                        $rows = \Illuminate\Support\Facades\DB::table('resume_overrides')
                            ->where('resume_id', $resume->id)
                            ->get();
                        foreach ($rows as $row) {
                            \Illuminate\Support\Facades\DB::table('resume_overrides')->insert([
                                'resume_id' => $copy->id,
                                'overridable_type' => $row->overridable_type,
                                'overridable_id' => $row->overridable_id,
                                'field_name' => $row->field_name,
                                'override_value' => $row->override_value,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            return $copy->load(['sections.items']);
        });
    }
}
