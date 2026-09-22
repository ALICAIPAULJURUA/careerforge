<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Skill::class);
        $skills = $request->user()->skills()->orderBy('category')->orderBy('name')->get()->groupBy('category');
        $allSkills = $request->user()->skills()->latest()->get();
        return view('skills.index', compact('skills', 'allSkills'));
    }

    public function create()
    {
        $this->authorize('create', Skill::class);
        $categories = ['technical','it','software_tools','soft','digital_media','leadership','other'];
        return view('skills.create', compact('categories'));
    }

    public function store(StoreSkillRequest $request)
    {
        $this->authorize('create', Skill::class);
        $request->user()->skills()->create($request->validated());
        return redirect()->route('skills.index')->with('status', 'Skill created.');
    }

    public function edit(Skill $skill)
    {
        $this->authorize('view', $skill);
        $categories = ['technical','it','software_tools','soft','digital_media','leadership','other'];
        return view('skills.edit', compact('skill', 'categories'));
    }

    public function update(UpdateSkillRequest $request, Skill $skill)
    {
        $this->authorize('update', $skill);
        $skill->update($request->validated());
        return redirect()->route('skills.index')->with('status', 'Skill updated.');
    }

    public function destroy(Skill $skill)
    {
        $this->authorize('delete', $skill);
        $skill->delete();
        return redirect()->route('skills.index')->with('status', 'Skill deleted.');
    }
}
