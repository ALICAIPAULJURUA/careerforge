<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExperienceAchievementRequest;
use App\Http\Requests\UpdateExperienceAchievementRequest;
use App\Models\Experience;
use App\Models\ExperienceAchievement;
use Illuminate\Http\Request;

class ExperienceAchievementController extends Controller
{
    public function index(Experience $experience)
    {
        $this->authorize('view', $experience);
        return redirect()->route('experiences.edit', $experience);
    }

    public function create(Experience $experience)
    {
        $this->authorize('view', $experience);
        return redirect()->route('experiences.edit', $experience);
    }

    public function edit(Experience $experience, ExperienceAchievement $achievement)
    {
        $this->authorize('view', $achievement);
        return redirect()->route('experiences.edit', $achievement->experience);
    }

    public function store(StoreExperienceAchievementRequest $request, Experience $experience)
    {
        $this->authorize('view', $experience);
        // Achievement creation checks via parent experience ownership; also gate via achievement view
        $data = $request->validated();

        // Determine next sort_order if not provided
        if (! isset($data['sort_order'])) {
            $max = $experience->achievements()->max('sort_order') ?? -1;
            $data['sort_order'] = $max + 1;
        }

        $achievement = $experience->achievements()->create($data);

        if ($request->expectsJson()) {
            return response()->json($achievement, 201);
        }

        return redirect()->route('experiences.edit', $experience)->with('status', 'Achievement added.');
    }

    public function update(UpdateExperienceAchievementRequest $request, ExperienceAchievement $achievement)
    {
        $this->authorize('update', $achievement);

        $achievement->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json($achievement);
        }

        return redirect()->route('experiences.edit', $achievement->experience)->with('status', 'Achievement updated.');
    }

    public function destroy(ExperienceAchievement $achievement)
    {
        $this->authorize('delete', $achievement);
        $experienceId = $achievement->experience_id;
        $achievement->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('experiences.edit', $experienceId)->with('status', 'Achievement deleted.');
    }
}
