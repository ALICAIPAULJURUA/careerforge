<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        $projects = $request->user()->projects()->with('technologies')->latest()->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);
        $data = $request->validated();
        $technologies = $data['technologies'] ?? [];
        unset($data['technologies']);

        $project = $request->user()->projects()->create($data);

        foreach ($technologies as $idx => $tech) {
            if (empty($tech['name'])) continue;
            $project->technologies()->create([
                'name' => $tech['name'],
                'sort_order' => $tech['sort_order'] ?? $idx,
            ]);
        }

        return redirect()->route('projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project)
    {
        $this->authorize('view', $project);
        $project->load('technologies');
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $data = $request->validated();
        $technologies = $data['technologies'] ?? [];
        unset($data['technologies']);

        $project->update($data);

        foreach ($technologies as $techData) {
            if (!empty($techData['_delete'])) {
                if (!empty($techData['id'])) {
                    $tech = $project->technologies()->where('id', $techData['id'])->first();
                    if ($tech) {
                        $this->authorize('delete', $tech);
                        $tech->delete();
                    }
                }
                continue;
            }
            if (!empty($techData['id'])) {
                $tech = $project->technologies()->where('id', $techData['id'])->first();
                if ($tech) {
                    $this->authorize('update', $tech);
                    $tech->update([
                        'name' => $techData['name'],
                        'sort_order' => $techData['sort_order'] ?? $tech->sort_order,
                    ]);
                }
            } else {
                if (empty($techData['name'])) continue;
                $this->authorize('create', $project);
                $project->technologies()->create([
                    'name' => $techData['name'],
                    'sort_order' => $techData['sort_order'] ?? 0,
                ]);
            }
        }

        return redirect()->route('projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }
}
