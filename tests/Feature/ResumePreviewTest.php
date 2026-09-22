<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);
    }

    public function test_guest_cannot_preview(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $this->get(route('resumes.preview', $resume))->assertRedirect(route('login'));
    }

    public function test_preview_shows_personal_and_sections_in_order(): void
    {
        $user = User::factory()->create(['name' => 'Alice']);
        $user->profile()->create([
            'full_name' => 'Alice Wonderland',
            'professional_title' => 'Engineer',
            'phone' => '123',
        ]);

        $resume = $user->resumes()->create([
            'name' => 'Preview Resume',
            'target_role' => 'Backend',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        // Create sections out of order to test ordering
        $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 2]);
        $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $resume->sections()->create(['section_type' => 'education', 'is_visible' => true, 'sort_order' => 1]);

        // Add items
        $exp = $user->experiences()->create(['job_title' => 'Senior Dev', 'organization' => 'Acme', 'start_date' => '2020-01-01', 'is_current' => true]);
        $ach = $exp->achievements()->create(['content' => 'Shipped X', 'sort_order' => 0]);
        $skill = $user->skills()->create(['name' => 'PHP', 'category' => 'technical']);
        $edu = $user->educations()->create(['institution' => 'MIT', 'qualification' => 'BSc', 'start_date' => '2015-01-01', 'is_current' => false, 'end_date' => '2019-01-01']);

        $expSection = $resume->sections()->where('section_type', 'experience')->first();
        $expSection->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);
        $expSection->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $ach->id, 'sort_order' => 0]);
        $skillSection = $resume->sections()->where('section_type', 'skills')->first();
        $skillSection->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $skill->id, 'sort_order' => 0]);
        $eduSection = $resume->sections()->where('section_type', 'education')->first();
        $eduSection->items()->create(['itemable_type' => \App\Models\Education::class, 'itemable_id' => $edu->id, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get(route('resumes.preview', $resume));
        $response->assertOk();
        // Check personal
        $response->assertSee('Alice Wonderland');
        $response->assertSee('Engineer');
        $response->assertSee('Senior Dev');
        $response->assertSee('Acme');
        $response->assertSee('Shipped X');
        $response->assertSee('PHP');
        $response->assertSee('MIT');
        $response->assertSee('BSc');
        // Check ordering: Experience should appear before Education before Skills in HTML
        $content = $response->getContent();
        $posExp = strpos($content, 'Experience');
        $posEdu = strpos($content, 'Education');
        $posSkills = strpos($content, 'Skills');
        $this->assertNotFalse($posExp);
        $this->assertNotFalse($posEdu);
        $this->assertNotFalse($posSkills);
        $this->assertTrue($posExp < $posEdu && $posEdu < $posSkills, 'Sections should be ordered by sort_order');
    }

    public function test_preview_excludes_hidden_sections_and_unselected_items(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Hidden Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $resume->sections()->create(['section_type' => 'skills', 'is_visible' => false, 'sort_order' => 0]);
        $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 1]);

        $skill = $user->skills()->create(['name' => 'HiddenSkill', 'category' => 'technical']);
        $exp = $user->experiences()->create(['job_title' => 'VisibleJob', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $expUnselected = $user->experiences()->create(['job_title' => 'HiddenJob', 'organization' => 'Org2', 'start_date' => '2019-01-01', 'is_current' => true]);

        $skillSection = $resume->sections()->where('section_type', 'skills')->first();
        $skillSection->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $skill->id, 'sort_order' => 0]);
        $expSection = $resume->sections()->where('section_type', 'experience')->first();
        $expSection->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);
        // HiddenJob not attached

        $response = $this->actingAs($user)->get(route('resumes.preview', $resume));
        $response->assertOk();
        $response->assertDontSee('HiddenJob'); // unselected not shown
        $response->assertDontSee('HiddenSkill'); // hidden section's item not shown even though attached
        $response->assertSee('VisibleJob');
        // Hidden section title should not appear
        $content = $response->getContent();
        $this->assertStringNotContainsString('>Skills<', $content); // section heading for hidden skills should be absent
    }

    public function test_preview_unauthorized_blocked(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $resume = $owner->resumes()->create([
            'name' => 'Owner Resume',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $this->actingAs($attacker)->get(route('resumes.preview', $resume))->assertStatus(403);
    }

    public function test_preview_empty_resume_shows_message(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Empty',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $response = $this->actingAs($user)->get(route('resumes.preview', $resume));
        $response->assertOk();
        $response->assertSee('No sections configured');
    }

    public function test_preview_uses_modern_template_and_theme_colors(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Theme Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $response = $this->actingAs($user)->get(route('resumes.preview', $resume));
        $response->assertOk();
        $response->assertSee('--primary-color: #2563eb');
        $response->assertSee('--font-family:');
    }
}
