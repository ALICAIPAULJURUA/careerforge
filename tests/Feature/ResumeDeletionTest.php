<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);
    }

    public function test_guest_cannot_delete(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $this->delete(route('resumes.destroy', $resume))->assertRedirect(route('login'));
    }

    public function test_deleting_resume_removes_sections_and_items_but_leaves_masters(): void
    {
        $user = User::factory()->create();

        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $ach = $exp->achievements()->create(['content' => 'Did X', 'sort_order' => 0]);
        $skill = $user->skills()->create(['name' => 'PHP', 'category' => 'technical']);
        $edu = $user->educations()->create(['institution' => 'Uni', 'qualification' => 'BSc', 'start_date' => '2015-01-01', 'is_current' => false, 'end_date' => '2019-01-01']);
        $project = $user->projects()->create(['name' => 'Proj']);
        $project->technologies()->create(['name' => 'Laravel', 'sort_order' => 0]);

        $resume = $user->resumes()->create([
            'name' => 'To Delete',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $expSection = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $expSection->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);
        $expSection->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $ach->id, 'sort_order' => 1]);

        $skillSection = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 1]);
        $skillSection->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $skill->id, 'sort_order' => 0]);

        $eduSection = $resume->sections()->create(['section_type' => 'education', 'is_visible' => true, 'sort_order' => 2]);
        $eduSection->items()->create(['itemable_type' => \App\Models\Education::class, 'itemable_id' => $edu->id, 'sort_order' => 0]);

        $projSection = $resume->sections()->create(['section_type' => 'projects', 'is_visible' => true, 'sort_order' => 3]);
        $projSection->items()->create(['itemable_type' => \App\Models\Project::class, 'itemable_id' => $project->id, 'sort_order' => 0]);

        $expCountBefore = \App\Models\Experience::count();
        $achCountBefore = \App\Models\ExperienceAchievement::count();
        $skillCountBefore = \App\Models\Skill::count();
        $eduCountBefore = \App\Models\Education::count();
        $projCountBefore = \App\Models\Project::count();
        $techCountBefore = \App\Models\ProjectTechnology::count();
        $sectionCountBefore = \App\Models\ResumeSection::count();
        $itemCountBefore = \App\Models\ResumeItem::count();

        $this->assertEquals(4, $sectionCountBefore);
        $this->assertEquals(5, $itemCountBefore);

        $response = $this->actingAs($user)->delete(route('resumes.destroy', $resume));
        $response->assertRedirect(route('resumes.index'));

        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
        $this->assertDatabaseCount('resume_sections', 0);
        $this->assertDatabaseCount('resume_items', 0);

        // Masters must be untouched – explicit per Phase 5/6 contract
        $this->assertEquals($expCountBefore, \App\Models\Experience::count());
        $this->assertEquals($achCountBefore, \App\Models\ExperienceAchievement::count());
        $this->assertEquals($skillCountBefore, \App\Models\Skill::count());
        $this->assertEquals($eduCountBefore, \App\Models\Education::count());
        $this->assertEquals($projCountBefore, \App\Models\Project::count());
        $this->assertEquals($techCountBefore, \App\Models\ProjectTechnology::count());

        $this->assertDatabaseHas('experiences', ['id' => $exp->id, 'job_title' => 'Dev']);
        $this->assertDatabaseHas('experience_achievements', ['id' => $ach->id, 'content' => 'Did X']);
        $this->assertDatabaseHas('skills', ['id' => $skill->id]);
        $this->assertDatabaseHas('educations', ['id' => $edu->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_deleting_one_resume_does_not_affect_another(): void
    {
        $user = User::factory()->create();
        $template = Template::first();
        $theme = Theme::first();

        $r1 = $user->resumes()->create(['name' => 'R1', 'template_id' => $template->id, 'theme_id' => $theme->id]);
        $r2 = $user->resumes()->create(['name' => 'R2', 'template_id' => $template->id, 'theme_id' => $theme->id]);

        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        foreach ([$r1, $r2] as $r) {
            $s = $r->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
            $s->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);
        }

        $this->actingAs($user)->delete(route('resumes.destroy', $r1))->assertRedirect(route('resumes.index'));

        $this->assertDatabaseMissing('resumes', ['id' => $r1->id]);
        $this->assertDatabaseHas('resumes', ['id' => $r2->id]);
        $this->assertEquals(1, $r2->fresh()->sections()->count());
        $this->assertEquals(1, $r2->fresh()->sections()->first()->items()->count());
    }

    public function test_unauthorized_user_cannot_delete_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $resume = $owner->resumes()->create([
            'name' => 'Owner Resume',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $exp = $owner->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $section = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);

        $this->actingAs($attacker)->delete(route('resumes.destroy', $resume))->assertStatus(403);

        $this->assertDatabaseHas('resumes', ['id' => $resume->id]);
        $this->assertDatabaseHas('resume_sections', ['id' => $section->id]);
        $this->assertDatabaseHas('experiences', ['id' => $exp->id]);
    }

    public function test_deleting_nonexistent_returns_404(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->delete(route('resumes.destroy', 9999))->assertStatus(404);
    }

    public function test_deleted_resume_sections_and_items_cascade_via_fk(): void
    {
        // Directly test FK cascade: delete resume via model, ensure DB cascade works without controller
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Cascade Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $section = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 0]);
        $skill = $user->skills()->create(['name' => 'PHP', 'category' => 'technical']);
        $section->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $skill->id, 'sort_order' => 0]);

        $resumeId = $resume->id;
        $sectionId = $section->id;

        $resume->delete();

        $this->assertDatabaseMissing('resume_sections', ['id' => $sectionId]);
        $this->assertDatabaseMissing('resume_sections', ['resume_id' => $resumeId]);
        $this->assertDatabaseMissing('resume_items', ['resume_section_id' => $sectionId]);
        $this->assertDatabaseHas('skills', ['id' => $skill->id]);
    }
}
