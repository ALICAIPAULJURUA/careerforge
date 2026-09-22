<?php

namespace App\Actions\Resume;

use App\Models\ResumeSection;
use Illuminate\Support\Facades\DB;

class ReorderResumeItemsAction
{
    /**
     * @param int[] $orderedIds ordered list of resume_item ids
     */
    public function __invoke(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                \App\Models\ResumeItem::where('id', $id)->update(['sort_order' => $index]);
            }
        });
    }

    public function forSection(ResumeSection $section, array $orderedIds): void
    {
        // Ensure all ids belong to section
        $count = $section->items()->whereIn('id', $orderedIds)->count();
        if ($count !== count($orderedIds)) {
            throw new \InvalidArgumentException('One or more items do not belong to this section.');
        }

        $this->__invoke($orderedIds);
    }
}
