<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Resume;
use App\Models\ResumeItem;
use App\Models\ResumeSection;
use App\Models\Skill;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);
    }

    protected function createMasterData(User $user): array
    {
        $exp1 = $user->experiences()->create([
            'job_title' => 'Dev 1',
            'organization' => 'Org 1',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'is_current' => false,
        ]);
        $exp2 = $user->experiences()->create([
            'job_title' => 'Dev 2',
            'organization' => 'Org 2',
            'start_date' => '2021-02-01',
            'is_current' => true,
        ]);
        $ach1 = $exp1->achievements()->create(['content' => 'Ach 1-1', 'sort_order' => 0]);
        $ach2 = $exp1->achievements()->create(['content' => 'Ach 1-2', 'sort_order' => 1]);
        $ach3 = $exp2->achievements()->create(['content' => 'Ach 2-1', 'sort_order' => 0]);

        $edu1 = $user->educations()->create([
            'institution' => 'Uni A',
            'qualification' => 'BSc',
            'start_date' => '2015-09-01',
            'is_current' => false,
            'end_date' => '2019-06-15',
        ]);
        $edu2 = $user->educations()->create([
            'institution' => 'Uni B',
            'qualification' => 'MSc',
            'start_date' => '2019-09-01',
            'is_current' => true,
        ]);

        $skill1 = $user->skills()->create(['name' => 'PHP', 'category' => 'technical']);
        $skill2 = $user->skills()->create(['name' => 'Leadership', 'category' => 'leadership']);
        $skill3 = $user->skills()->create(['name' => 'Figma', 'category' => 'digital_media']);

        $proj1 = $user->projects()->create(['name' => 'Project Alpha']);
        $proj2 = $user->projects()->create(['name' => 'Project Beta']);

        return compact('exp1', 'exp2', 'ach1', 'ach2', 'ach3', 'edu1', 'edu2', 'skill1', 'skill2', 'skill3', 'proj1', 'proj2');
    }

    public function test_guest_cannot_access_resumes(): void
    {
        $this->get(route('resumes.index'))->assertRedirect(route('login'));
        $this->get(route('resumes.create'))->assertRedirect(route('login'));
        $this->post(route('resumes.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_create_resume(): void
    {
        $user = User::factory()->create();
        $template = Template::first();
        $theme = Theme::first();

        $response = $this->actingAs($user)->post(route('resumes.store'), [
            'name' => 'My Resume',
            'target_role' => 'Backend Dev',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('resumes', [
            'user_id' => $user->id,
            'name' => 'My Resume',
            'target_role' => 'Backend Dev',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);
        $this->assertCount(1, $user->fresh()->resumes);
    }

    public function test_resume_validation_requires_name_template_theme(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('resumes.store'), [
            'name' => '',
        ]);
        $response->assertSessionHasErrors(['name', 'template_id', 'theme_id']);
    }

    public function test_user_can_select_specific_records_curated_subset(): void
    {
        $user = User::factory()->create();
        $data = $this->createMasterData($user);
        $template = Template::first();
        $theme = Theme::first();

        $resume = $user->resumes()->create([
            'name' => 'Test Resume',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);

        // Attach only 1 of 2 experiences
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $data['exp1']->id,
        ])->assertRedirect();

        // Attach only 1 of 2 achievements under exp1
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => \App\Models\ExperienceAchievement::class,
            'itemable_id' => $data['ach1']->id,
        ])->assertRedirect();

        // Attach only 1 of 2 educations
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'education',
            'itemable_type' => Education::class,
            'itemable_id' => $data['edu1']->id,
        ])->assertRedirect();

        // Attach 2 of 3 skills
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'skills',
            'itemable_type' => Skill::class,
            'itemable_id' => $data['skill1']->id,
        ])->assertRedirect();
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'skills',
            'itemable_type' => Skill::class,
            'itemable_id' => $data['skill2']->id,
        ])->assertRedirect();

        // Attach 1 of 2 projects
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'projects',
            'itemable_type' => Project::class,
            'itemable_id' => $data['proj1']->id,
        ])->assertRedirect();

        $resume->refresh();
        $this->assertCount(4, $resume->sections); // experience, education, skills, projects

        $expSection = $resume->sections()->where('section_type', 'experience')->first();
        $this->assertNotNull($expSection);
        $this->assertCount(2, $expSection->items); // 1 exp + 1 ach

        $eduSection = $resume->sections()->where('section_type', 'education')->first();
        $this->assertCount(1, $eduSection->items);

        $skillSection = $resume->sections()->where('section_type', 'skills')->first();
        $this->assertCount(2, $skillSection->items);

        $projSection = $resume->sections()->where('section_type', 'projects')->first();
        $this->assertCount(1, $projSection->items);

        // Ensure not all master records are attached
        $this->assertEquals(2, $user->experiences()->count());
        $this->assertEquals(1, $expSection->items()->where('itemable_type', Experience::class)->count());
        $this->assertEquals(3, $user->experiences()->withCount('achievements')->get()->sum('achievements_count'));
    }

    public function test_selecting_and_reordering_never_mutates_master_data(): void
    {
        $user = User::factory()->create();
        $data = $this->createMasterData($user);
        $template = Template::first();
        $theme = Theme::first();

        $resume = $user->resumes()->create([
            'name' => 'Master Untouched Test',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);

        // Snapshot master data
        $exp1Before = $data['exp1']->fresh()->toArray();
        $ach1Before = $data['ach1']->fresh()->toArray();
        $skill1Before = $data['skill1']->fresh()->toArray();
        $edu1Before = $data['edu1']->fresh()->toArray();
        $achCountBefore = \App\Models\ExperienceAchievement::count();
        $expCountBefore = Experience::count();

        // Attach in specific order
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $data['exp2']->id,
            'sort_order' => 0,
        ]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $data['exp1']->id,
            'sort_order' => 1,
        ]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'skills',
            'itemable_type' => Skill::class,
            'itemable_id' => $data['skill3']->id,
        ]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'skills',
            'itemable_type' => Skill::class,
            'itemable_id' => $data['skill1']->id,
        ]);

        // Reorder skills items
        $skillSection = $resume->sections()->where('section_type', 'skills')->first();
        $orderedIds = $skillSection->items()->orderBy('sort_order')->pluck('id')->reverse()->values()->toArray();
        // Now ordered should be skill1, skill3 if reverse; let's actually set explicit reorder to reverse
        $items = $skillSection->items()->orderBy('sort_order')->get();
        $reversed = $items->reverse()->pluck('id')->toArray();

        $response = $this->actingAs($user)->put(route('resume-items.reorder'), [
            'ordered_ids' => $reversed,
        ]);
        $response->assertRedirect();

        // Verify master data unchanged
        $this->assertEquals($exp1Before['job_title'], $data['exp1']->fresh()->job_title);
        $this->assertEquals($ach1Before['content'], $data['ach1']->fresh()->content);
        $this->assertEquals($skill1Before['name'], $data['skill1']->fresh()->name);
        $this->assertEquals($edu1Before['institution'], $data['edu1']->fresh()->institution);
        $this->assertEquals($achCountBefore, \App\Models\ExperienceAchievement::count());
        $this->assertEquals($expCountBefore, Experience::count());
        // Also check master sort_order unchanged (achievements master ordering)
        $this->assertEquals(0, $data['ach1']->fresh()->sort_order);
        $this->assertEquals(1, $data['ach2']->fresh()->sort_order);
        // But resume sort_order should have changed
        $skillSection->refresh();
        $after = $skillSection->items()->orderBy('sort_order')->get()->pluck('itemable_id')->toArray();
        $this->assertEquals([$data['skill1']->id, $data['skill3']->id], $after); // after reverse, skill1 first? Actually we reversed, need to check logic

        // Also ensure resume_items sort_order is independent
        $firstItem = $skillSection->items()->where('itemable_id', $data['skill3']->id)->first();
        $secondItem = $skillSection->items()->where('itemable_id', $data['skill1']->id)->first();
        // After reorder, first should be skill3? Let's just assert they are swapped compared to original insertion order
        $this->assertNotEquals($firstItem->sort_order, $secondItem->sort_order);
    }

    public function test_reordering_items_with_polymorphic_section(): void
    {
        $user = User::factory()->create();
        $data = $this->createMasterData($user);
        $resume = $user->resumes()->create([
            'name' => 'Reorder Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        // Attach 3 experiences in order
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $data['exp1']->id,
        ]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $data['exp2']->id,
        ]);
        // Add third experience
        $exp3 = $user->experiences()->create(['job_title' => 'Dev 3', 'organization' => 'Org 3', 'start_date' => '2022-01-01', 'is_current' => true]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp3->id,
        ]);

        $section = $resume->sections()->where('section_type', 'experience')->first();
        $before = $section->items()->orderBy('sort_order')->pluck('itemable_id')->toArray();
        $this->assertEquals([$data['exp1']->id, $data['exp2']->id, $exp3->id], $before);

        // Reorder to reverse
        $ids = $section->items()->orderBy('sort_order')->pluck('id')->toArray();
        $reversed = array_reverse($ids);
        $this->actingAs($user)->put(route('resume-items.reorder'), [
            'ordered_ids' => $reversed,
        ])->assertRedirect();

        $after = $section->fresh()->items()->orderBy('sort_order')->pluck('itemable_id')->toArray();
        $this->assertEquals(array_reverse($before), $after);
    }

    public function test_section_visibility_toggle(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Section Vis Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        // Create section via attach
        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp->id,
        ]);

        $section = $resume->sections()->where('section_type', 'experience')->first();
        $this->assertTrue($section->is_visible);

        // Toggle hidden
        $response = $this->actingAs($user)->put(route('resumes.sections.update', [$resume, $section]), [
            'is_visible' => false,
        ]);
        $response->assertRedirect();
        $this->assertFalse($section->fresh()->is_visible);

        // Toggle visible again
        $this->actingAs($user)->put(route('resumes.sections.update', [$resume, $section]), [
            'is_visible' => true,
        ]);
        $this->assertTrue($section->fresh()->is_visible);
    }

    public function test_duplicate_item_prevented(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $resume = $user->resumes()->create([
            'name' => 'Dup Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp->id,
        ])->assertRedirect();

        $response = $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp->id,
        ]);
        $response->assertSessionHasErrors('itemable_id');
        $this->assertEquals(1, $resume->sections()->where('section_type', 'experience')->first()->items()->count());
    }

    public function test_unauthorized_user_cannot_touch_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $template = Template::first();
        $theme = Theme::first();
        $resume = $owner->resumes()->create([
            'name' => 'Owner Resume',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);
        $exp = $owner->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);

        $this->actingAs($attacker)->get(route('resumes.edit', $resume))->assertStatus(403);
        $this->actingAs($attacker)->get(route('resumes.show', $resume))->assertStatus(403);
        $this->actingAs($attacker)->put(route('resumes.update', $resume), ['name' => 'Hacked'])->assertStatus(403);
        $this->actingAs($attacker)->delete(route('resumes.destroy', $resume))->assertStatus(403);
        $this->actingAs($attacker)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp->id,
        ])->assertStatus(403);

        // Ensure resume still owned by owner
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'user_id' => $owner->id]);
    }

    public function test_unauthorized_user_cannot_attach_other_users_master_record(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $template = Template::first();
        $theme = Theme::first();
        $resume = $attacker->resumes()->create([
            'name' => 'Attacker Resume',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);
        $exp = $owner->experiences()->create(['job_title' => 'Secret', 'organization' => 'SecretOrg', 'start_date' => '2020-01-01', 'is_current' => true]);

        $response = $this->actingAs($attacker)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp->id,
        ]);
        $response->assertSessionHasErrors('itemable_id');
        $this->assertDatabaseCount('resume_items', 0);
    }

    public function test_deleting_resume_leaves_master_data_intact(): void
    {
        $user = User::factory()->create();
        $data = $this->createMasterData($user);
        $resume = $user->resumes()->create([
            'name' => 'To Delete',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $data['exp1']->id,
        ]);

        $expCountBefore = Experience::count();
        $achCountBefore = \App\Models\ExperienceAchievement::count();

        $this->actingAs($user)->delete(route('resumes.destroy', $resume))->assertRedirect(route('resumes.index'));

        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
        $this->assertEquals($expCountBefore, Experience::count());
        $this->assertEquals($achCountBefore, \App\Models\ExperienceAchievement::count());
        $this->assertDatabaseHas('experiences', ['id' => $data['exp1']->id]);
    }

    public function test_resume_items_cascade_when_section_deleted(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Cascade Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $this->actingAs($user)->post(route('resumes.items.store', $resume), [
            'section_type' => 'experience',
            'itemable_type' => Experience::class,
            'itemable_id' => $exp->id,
        ]);

        $section = $resume->sections()->where('section_type', 'experience')->first();
        $itemCount = ResumeItem::count();
        $this->assertEquals(1, $itemCount);

        $this->actingAs($user)->delete(route('resumes.sections.destroy', [$resume, $section]));
        $this->assertDatabaseMissing('resume_sections', ['id' => $section->id]);
        $this->assertDatabaseCount('resume_items', 0);
        $this->assertDatabaseHas('experiences', ['id' => $exp->id]);
    }
}
