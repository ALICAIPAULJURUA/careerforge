<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeSection extends Model
{
    protected $fillable = [
        'resume_id',
        'section_type',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ResumeItem::class)->orderBy('sort_order');
    }

    public function resumeItems(): HasMany
    {
        return $this->hasMany(ResumeItem::class);
    }
}
