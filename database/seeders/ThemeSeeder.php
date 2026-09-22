<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Theme::firstOrCreate(
            ['name' => 'Default'],
            [
                'primary_color' => '#2563eb',
                'secondary_color' => '#64748b',
                'text_color' => '#1e293b',
                'background_color' => '#ffffff',
                'sidebar_color' => null,
                'font_family' => 'Inter',
                'heading_size' => 'md',
                'body_size' => 'md',
                'is_system' => true,
            ]
        );
    }
}
