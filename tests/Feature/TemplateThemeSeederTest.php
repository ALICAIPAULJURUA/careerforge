<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Theme;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateThemeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_seeder_creates_modern_template(): void
    {
        $this->assertDatabaseCount('templates', 0);

        $this->seed(TemplateSeeder::class);

        $this->assertDatabaseCount('templates', 1);
        $this->assertDatabaseHas('templates', [
            'key' => 'modern',
            'name' => 'Modern',
            'supports_photo' => true,
            'is_active' => true,
        ]);

        $template = Template::where('key', 'modern')->first();
        $this->assertNotNull($template);
        $this->assertEquals('modern', $template->key);
        $this->assertTrue($template->supports_photo);
        $this->assertTrue($template->is_active);
    }

    public function test_theme_seeder_creates_default_theme(): void
    {
        $this->assertDatabaseCount('themes', 0);

        $this->seed(ThemeSeeder::class);

        $this->assertDatabaseCount('themes', 1);
        $this->assertDatabaseHas('themes', [
            'name' => 'Default',
            'primary_color' => '#2563eb',
            'secondary_color' => '#64748b',
            'text_color' => '#1e293b',
            'background_color' => '#ffffff',
            'font_family' => 'Inter',
            'heading_size' => 'md',
            'body_size' => 'md',
            'is_system' => true,
        ]);

        $theme = Theme::where('name', 'Default')->first();
        $this->assertNotNull($theme);
        $this->assertEquals('#2563eb', $theme->primary_color);
        $this->assertTrue($theme->is_system);
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed(TemplateSeeder::class);
        $this->seed(TemplateSeeder::class);
        $this->assertDatabaseCount('templates', 1);

        $this->seed(ThemeSeeder::class);
        $this->seed(ThemeSeeder::class);
        $this->assertDatabaseCount('themes', 1);
    }

    public function test_database_seeder_calls_both(): void
    {
        $this->seed();

        $this->assertDatabaseHas('templates', ['key' => 'modern']);
        $this->assertDatabaseHas('themes', ['name' => 'Default']);
        $this->assertEquals(1, Template::count());
        $this->assertEquals(1, Theme::count());
    }

    public function test_templates_and_themes_exist_for_future_resume_creation(): void
    {
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);

        $template = Template::first();
        $theme = Theme::first();

        $this->assertNotNull($template, 'At least one template must exist for Phase 5');
        $this->assertNotNull($theme, 'At least one theme must exist for Phase 5');
        $this->assertTrue($template->is_active);
        // Simulate what Phase 5 will do: create resume referencing them
        $user = \App\Models\User::factory()->create();
        // Just verify FK would succeed – we don't create resume yet, but check IDs are valid
        $this->assertIsInt($template->id);
        $this->assertIsInt($theme->id);
    }
}
