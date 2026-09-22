<?php

namespace Tests\Unit;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use App\Services\ResumeRendering\ResumeRenderingService;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeRenderingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);
    }

    protected function makeResumeWithMasters(User $user): Resume
    {
        $template = Template::first();
        $theme = Theme::first();
        return $user->resumes()->create([
            'name' => 'Test Resume',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);
    }

    public function test_sections_are_ordered_by_sort_order_and_only_visible(): void
    {
        $user = User::factory()->create();
        $resume = $this->makeResumeWithMasters($user);

        // Create sections out of order
        $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 2]);
        $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $resume->sections()->create(['section_type' => 'education', 'is_visible' => false, 'sort_order' => 1]); // hidden
        $resume->sections()->create(['section_type' => 'projects', 'is_visible' => true, 'sort_order' => 1]);

        $service = app(ResumeRenderingService::class);
        $result = $service->render($resume);

        $this->assertEquals(['experience', 'projects', 'skills'], array_column($result->sections, 'type'));
        $this->assertNotContains('education', array_column($result->sections, 'type'));
        $this->assertEquals('Experience', $result->sections[0]['title']);
        $this->assertEquals(0, $result->sections[0]['sort_order']);
        $this->assertEquals(1, $result->sections[1]['sort_order']);
        $this->assertEquals(2, $result->sections[2]['sort_order']);
    }

    public function test_items_are_ordered_by_sort_order(): void
    {
        $user = User::factory()->create();
        $resume = $this->makeResumeWithMasters($user);

        $section = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 0]);

        $skillA = $user->skills()->create(['name' => 'A', 'category' => 'technical']);
        $skillB = $user->skills()->create(['name' => 'B', 'category' => 'technical']);
        $skillC = $user->skills()->create(['name' => 'C', 'category' => 'technical']);

        // Attach in order B, A, C but with explicit sort_order
        $section->items()->create(['itemable_type' => Skill::class, 'itemable_id' => $skillB->id, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => Skill::class, 'itemable_id' => $skillA->id, 'sort_order' => 1]);
        $section->items()->create(['itemable_type' => Skill::class, 'itemable_id' => $skillC->id, 'sort_order' => 2]);

        $service = app(ResumeRenderingService::class);
        $result = $service->render($resume);

        $skillSection = collect($result->sections)->firstWhere('type', 'skills');
        $this->assertNotNull($skillSection);
        $names = array_column($skillSection['items'], 'name');
        $this->assertEquals(['B', 'A', 'C'], $names);

        // Now reorder via different sort_order: C, B, A
        foreach ($section->items as $item) {
            if ($item->itemable_id === $skillC->id) $item->update(['sort_order' => 0]);
            if ($item->itemable_id === $skillB->id) $item->update(['sort_order' => 1]);
            if ($item->itemable_id === $skillA->id) $item->update(['sort_order' => 2]);
        }

        $resume->refresh();
        $result = $service->render($resume);
        $skillSection = collect($result->sections)->firstWhere('type', 'skills');
        $names = array_column($skillSection['items'], 'name');
        $this->assertEquals(['C', 'B', 'A'], $names);
    }

    public function test_achievement_grouping_under_parent_experience(): void
    {
        $user = User::factory()->create();
        $resume = $this->makeResumeWithMasters($user);

        $exp1 = $user->experiences()->create([
            'job_title' => 'Dev1',
            'organization' => 'Org1',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);
        $exp2 = $user->experiences()->create([
            'job_title' => 'Dev2',
            'organization' => 'Org2',
            'start_date' => '2021-01-01',
            'is_current' => true,
        ]);
        $ach1 = $exp1->achievements()->create(['content' => 'Ach 1-1', 'sort_order' => 0]);
        $ach2 = $exp1->achievements()->create(['content' => 'Ach 1-2', 'sort_order' => 1]);
        $ach3 = $exp2->achievements()->create(['content' => 'Ach 2-1', 'sort_order' => 0]);

        $section = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);

        // Select both experiences but only achievements 1-1 and 2-1 (not 1-2)
        $section->items()->create(['itemable_type' => Experience::class, 'itemable_id' => $exp1->id, 'sort_order' => 1]);
        $section->items()->create(['itemable_type' => Experience::class, 'itemable_id' => $exp2->id, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $ach1->id, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $ach3->id, 'sort_order' => 1]);
        // Note ach2 not selected

        $service = app(ResumeRenderingService::class);
        $result = $service->render($resume);

        $expSection = collect($result->sections)->firstWhere('type', 'experience');
        $this->assertNotNull($expSection);
        // Experiences should be ordered by resume sort_order: exp2 (0), exp1 (1)
        $this->assertEquals('Dev2', $expSection['items'][0]['job_title']);
        $this->assertEquals('Dev1', $expSection['items'][1]['job_title']);

        // Dev2 should have Ach 2-1
        $this->assertCount(1, $expSection['items'][0]['achievements']);
        $this->assertEquals('Ach 2-1', $expSection['items'][0]['achievements'][0]['content']);

        // Dev1 should have only Ach 1-1, not 1-2
        $this->assertCount(1, $expSection['items'][1]['achievements']);
        $this->assertEquals('Ach 1-1', $expSection['items'][1]['achievements'][0]['content']);

        // Orphan achievement test: add achievement without parent
        $achOrphan = $exp1->achievements()->create(['content' => 'Orphan', 'sort_order' => 2]);
        $section->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $achOrphan->id, 'sort_order' => 3]);
        // But also create a new experience not selected, its achievement should not appear alone
        $expNotSelected = $user->experiences()->create(['job_title' => 'Dev3', 'organization' => 'Org3', 'start_date' => '2022-01-01', 'is_current' => true]);
        $achNotSelectedParent = $expNotSelected->achievements()->create(['content' => 'Should not appear', 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $achNotSelectedParent->id, 'sort_order' => 4]);

        $result = $service->render($resume->fresh());
        $expSection = collect($result->sections)->firstWhere('type', 'experience');
        // Should still only have 2 experience items, orphan achievements grouped only if parent exists, but achNotSelectedParent's parent not in resume, so orphan should be ignored
        $this->assertCount(2, $expSection['items']);
        // Ach count for Dev1 now 2 (Ach 1-1 + Orphan) — Orphan's parent is Dev1 which is selected, so it should appear
        $dev1 = collect($expSection['items'])->firstWhere('job_title', 'Dev1');
        $this->assertCount(2, $dev1['achievements']);
        $this->assertEquals('Ach 1-1', $dev1['achievements'][0]['content']);
        $this->assertEquals('Orphan', $dev1['achievements'][1]['content']);
        // Verify the ach for Dev3 not selected does NOT appear anywhere
        $allAchContents = collect($expSection['items'])->flatMap(fn($e) => $e['achievements'])->pluck('content');
        $this->assertNotContains('Should not appear', $allAchContents);
    }

    public function test_empty_section_handling(): void
    {
        $user = User::factory()->create();
        $resume = $this->makeResumeWithMasters($user);

        $emptySection = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $hiddenSection = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => false, 'sort_order' => 1]);

        $service = app(ResumeRenderingService::class);
        $result = $service->render($resume);

        // Empty but visible section should still be returned with empty items
        $expSection = collect($result->sections)->firstWhere('type', 'experience');
        $this->assertNotNull($expSection);
        $this->assertEquals([], $expSection['items']);

        // Hidden section should not be returned
        $this->assertNull(collect($result->sections)->firstWhere('type', 'skills'));

        // No sections at all edge case
        $resume2 = $this->makeResumeWithMasters($user);
        $resume2->sections()->delete();
        $result2 = $service->render($resume2->fresh());
        $this->assertEquals([], $result2->sections);
    }

    public function test_render_returns_correct_meta_and_personal(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $user->profile()->create([
            'full_name' => 'Johnny Doe',
            'professional_title' => 'Engineer',
            'phone' => '123',
            'location' => 'Berlin',
        ]);

        $resume = $this->makeResumeWithMasters($user);
        $resume->update(['name' => 'My Resume', 'target_role' => 'Backend', 'photo_enabled' => true]);

        $service = app(ResumeRenderingService::class);
        $result = $service->render($resume);

        $this->assertEquals('My Resume', $result->meta['name']);
        $this->assertEquals('Backend', $result->meta['target_role']);
        $this->assertEquals('modern', $result->meta['template_key']);
        $this->assertIsArray($result->meta['theme']);
        $this->assertEquals('Johnny Doe', $result->personal['full_name']);
        $this->assertEquals('Engineer', $result->personal['professional_title']);
        $this->assertEquals('john@example.com', $result->personal['email']);
        $this->assertEquals('123', $result->personal['phone']);
        $this->assertEquals('Berlin', $result->personal['location']);
    }

    public function test_master_data_not_mutated_after_render(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create(['job_title' => 'Original', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $skill = $user->skills()->create(['name' => 'PHP', 'category' => 'technical']);
        $resume = $this->makeResumeWithMasters($user);
        $sectionExp = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $sectionExp->items()->create(['itemable_type' => Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);
        $sectionSkill = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 1]);
        $sectionSkill->items()->create(['itemable_type' => Skill::class, 'itemable_id' => $skill->id, 'sort_order' => 0]);

        $beforeExp = $exp->fresh()->toArray();
        $beforeSkill = $skill->fresh()->toArray();

        $service = app(ResumeRenderingService::class);
        $service->render($resume);

        $this->assertEquals($beforeExp['job_title'], $exp->fresh()->job_title);
        $this->assertEquals($beforeSkill['name'], $skill->fresh()->name);
    }

    public function test_project_technologies_included_and_ordered(): void
    {
        $user = User::factory()->create();
        $resume = $this->makeResumeWithMasters($user);
        $project = $user->projects()->create(['name' => 'Proj']);
        $project->technologies()->create(['name' => 'B', 'sort_order' => 1]);
        $project->technologies()->create(['name' => 'A', 'sort_order' => 0]);

        $section = $resume->sections()->create(['section_type' => 'projects', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => Project::class, 'itemable_id' => $project->id, 'sort_order' => 0]);

        $service = app(ResumeRenderingService::class);
        $result = $service->render($resume);
        $projSection = collect($result->sections)->firstWhere('type', 'projects');
        $this->assertNotNull($projSection);
        $techs = $projSection['items'][0]['technologies'];
        $this->assertEquals(['A', 'B'], array_column($techs, 'name'));
    }
}
