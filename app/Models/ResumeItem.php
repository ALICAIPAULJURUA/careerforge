<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ResumeItem extends Model
{
    protected $fillable = [
        'resume_section_id',
        'itemable_type',
        'itemable_id',
        'sort_order',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ResumeSection::class, 'resume_section_id');
    }

    public function resumeSection(): BelongsTo
    {
        return $this->belongsTo(ResumeSection::class, 'resume_section_id');
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
