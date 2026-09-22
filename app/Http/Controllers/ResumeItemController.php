<?php

namespace App\Http\Controllers;

use App\Actions\Resume\AttachResumeItemAction;
use App\Actions\Resume\ReorderResumeItemsAction;
use App\Http\Requests\ReorderResumeItemsRequest;
use App\Http\Requests\StoreResumeItemRequest;
use App\Models\Resume;
use App\Models\ResumeItem;
use Illuminate\Http\Request;

class ResumeItemController extends Controller
{
    public function store(StoreResumeItemRequest $request, Resume $resume, AttachResumeItemAction $action)
    {
        $this->authorize('view', $resume);

        $data = $request->validated();

        $item = $action(
            $resume,
            $data['section_type'],
            $data['itemable_type'],
            (int) $data['itemable_id'],
            $data['sort_order'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json($item, 201);
        }

        return redirect()->route('resumes.edit', $resume)->with('status', 'Item added.');
    }

    public function destroy(ResumeItem $resumeItem)
    {
        $this->authorize('delete', $resumeItem);
        $resumeId = $resumeItem->resumeSection->resume_id;
        $resumeItem->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('resumes.edit', $resumeId)->with('status', 'Item removed.');
    }

    public function reorder(ReorderResumeItemsRequest $request, ReorderResumeItemsAction $action)
    {
        // Check that all items belong to same user via first item's section
        $orderedIds = $request->validated()['ordered_ids'];
        $firstItem = ResumeItem::with('resumeSection.resume')->find($orderedIds[0] ?? null);
        if ($firstItem) {
            $this->authorize('update', $firstItem);
        }

        // Ensure all items belong to same resume section owner? Just use action
        // Verify each item belongs to requesting user
        foreach ($orderedIds as $id) {
            $item = ResumeItem::with('resumeSection.resume')->find($id);
            if ($item) {
                $this->authorize('update', $item);
            }
        }

        $action($orderedIds);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Items reordered.']);
        }

        // Redirect back to resume edit via first item's resume
        $resumeId = $firstItem?->resumeSection?->resume_id ?? null;
        if ($resumeId) {
            return redirect()->route('resumes.edit', $resumeId)->with('status', 'Items reordered.');
        }

        return response()->json(['message' => 'Items reordered.']);
    }
}
