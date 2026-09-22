<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Models\Education;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Education::class);
        $educations = $request->user()->educations()->latest('start_date')->get();
        return view('educations.index', compact('educations'));
    }

    public function create()
    {
        $this->authorize('create', Education::class);
        return view('educations.create');
    }

    public function store(StoreEducationRequest $request)
    {
        $this->authorize('create', Education::class);
        $data = $request->validated();
        $isCurrent = (bool) ($data['is_current'] ?? false);
        if ($isCurrent) {
            $data['end_date'] = null;
        }
        $data['is_current'] = $isCurrent;

        $request->user()->educations()->create($data);

        return redirect()->route('educations.index')->with('status', 'Education created.');
    }

    public function edit(Education $education)
    {
        $this->authorize('view', $education);
        return view('educations.edit', compact('education'));
    }

    public function update(UpdateEducationRequest $request, Education $education)
    {
        $this->authorize('update', $education);
        $data = $request->validated();
        $isCurrent = (bool) ($data['is_current'] ?? false);
        if ($isCurrent) {
            $data['end_date'] = null;
        }
        $data['is_current'] = $isCurrent;

        $education->update($data);

        return redirect()->route('educations.index')->with('status', 'Education updated.');
    }

    public function destroy(Education $education)
    {
        $this->authorize('delete', $education);
        $education->delete();
        return redirect()->route('educations.index')->with('status', 'Education deleted.');
    }
}
