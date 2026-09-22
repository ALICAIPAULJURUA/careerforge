<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'target_role',
        'template_id',
        'theme_id',
        'font_override',
        'photo_enabled',
        'photo_style',
        'layout',
        'ats_mode',
        'page_size',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'photo_enabled' => 'boolean',
            'ats_mode' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ResumeSection::class)->orderBy('sort_order');
    }

    // Alias for spec naming
    public function resumeSections(): HasMany
    {
        return $this->hasMany(ResumeSection::class);
    }
}
