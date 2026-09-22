<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateResumeSectionRequest;
use App\Models\Resume;
use App\Models\ResumeSection;
use Illuminate\Http\Request;

class ResumeSectionController extends Controller
{
    public function update(UpdateResumeSectionRequest $request, Resume $resume, ResumeSection $section)
    {
        $this->authorize('update', $section);
        // Ensure section belongs to resume
        if ($section->resume_id !== $resume->id) {
            abort(404);
        }
        $section->update($request->validated());
        if ($request->expectsJson()) {
            return response()->json($section);
        }
        return redirect()->route('resumes.edit', $resume)->with('status', 'Section updated.');
    }

    public function store(Request $request, Resume $resume)
    {
        $this->authorize('update', $resume);
        $data = $request->validate([
            'section_type' => ['required', 'string', 'in:summary,experience,education,skills,projects,certifications,awards,leadership,languages,references'],
            'is_visible' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $section = $resume->sections()->firstOrCreate(
            ['section_type' => $data['section_type']],
            [
                'is_visible' => $data['is_visible'] ?? true,
                'sort_order' => $data['sort_order'] ?? ($resume->sections()->max('sort_order') !== null ? $resume->sections()->max('sort_order') + 1 : 0),
            ]
        );

        // If already exists and is_visible provided, update
        if (isset($data['is_visible'])) {
            $section->update(['is_visible' => $data['is_visible']]);
        }

        if ($request->expectsJson()) {
            return response()->json($section, 201);
        }
        return redirect()->route('resumes.edit', $resume)->with('status', 'Section added.');
    }

    public function destroy(Resume $resume, ResumeSection $section)
    {
        $this->authorize('delete', $section);
        if ($section->resume_id !== $resume->id) abort(404);
        $section->delete();
        if (request()->expectsJson()) return response()->json(null, 204);
        return redirect()->route('resumes.edit', $resume)->with('status', 'Section removed.');
    }

    public function reorder(Request $request, Resume $resume)
    {
        $this->authorize('update', $resume);
        $data = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:resume_sections,id'],
        ]);

        foreach ($data['ordered_ids'] as $index => $id) {
            $section = $resume->sections()->where('id', $id)->first();
            if ($section) {
                $section->update(['sort_order' => $index]);
            }
        }

        if ($request->expectsJson()) return response()->json(['message' => 'Sections reordered.']);
        return redirect()->route('resumes.edit', $resume)->with('status', 'Sections reordered.');
    }
}
