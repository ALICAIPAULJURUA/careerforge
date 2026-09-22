<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'supports_photo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'supports_photo' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }
}
