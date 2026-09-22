<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Experience::class);

        $experiences = $request->user()->experiences()->with('achievements')->latest('start_date')->get();

        return view('experiences.index', compact('experiences'));
    }

    public function create()
    {
        $this->authorize('create', Experience::class);

        return view('experiences.create');
    }

    public function store(StoreExperienceRequest $request)
    {
        $this->authorize('create', Experience::class);

        $data = $request->validated();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);

        $isCurrent = (bool) ($data['is_current'] ?? false);
        if ($isCurrent) {
            $data['end_date'] = null;
        }
        $data['is_current'] = $isCurrent;

        $experience = $request->user()->experiences()->create($data);

        foreach ($achievements as $index => $achData) {
            if (empty($achData['content'])) {
                continue;
            }
            $experience->achievements()->create([
                'content' => $achData['content'],
                'sort_order' => $achData['sort_order'] ?? $index,
            ]);
        }

        return redirect()->route('experiences.index')->with('status', 'Experience created.');
    }

    public function edit(Experience $experience)
    {
        $this->authorize('view', $experience);
        $experience->load('achievements');

        return view('experiences.edit', compact('experience'));
    }

    public function update(UpdateExperienceRequest $request, Experience $experience)
    {
        $this->authorize('update', $experience);

        $data = $request->validated();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);

        $isCurrent = (bool) ($data['is_current'] ?? false);
        if ($isCurrent) {
            $data['end_date'] = null;
        }
        $data['is_current'] = $isCurrent;

        $experience->update($data);

        // Handle nested achievements: update, create, delete
        foreach ($achievements as $achData) {
            if (! empty($achData['_delete'])) {
                if (! empty($achData['id'])) {
                    $ach = $experience->achievements()->where('id', $achData['id'])->first();
                    if ($ach) {
                        $this->authorize('delete', $ach);
                        $ach->delete();
                    }
                }
                continue;
            }

            if (! empty($achData['id'])) {
                $ach = $experience->achievements()->where('id', $achData['id'])->first();
                if ($ach) {
                    $this->authorize('update', $ach);
                    $ach->update([
                        'content' => $achData['content'],
                        'sort_order' => $achData['sort_order'] ?? $ach->sort_order,
                    ]);
                }
            } else {
                if (empty($achData['content'])) {
                    continue;
                }
                $this->authorize('create', $experience);
                $experience->achievements()->create([
                    'content' => $achData['content'],
                    'sort_order' => $achData['sort_order'] ?? 0,
                ]);
            }
        }

        // Reorder achievements if sort_order provided: ensure ordering is respected
        // We keep as stored.

        return redirect()->route('experiences.index')->with('status', 'Experience updated.');
    }

    public function destroy(Experience $experience)
    {
        $this->authorize('delete', $experience);
        $experience->delete();

        return redirect()->route('experiences.index')->with('status', 'Experience deleted.');
    }
}
