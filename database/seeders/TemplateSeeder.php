<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Template::firstOrCreate(
            ['key' => 'modern'],
            [
                'name' => 'Modern',
                'description' => 'Clean single-column layout with accent colors',
                'supports_photo' => true,
                'is_active' => true,
            ]
        );
    }
}
