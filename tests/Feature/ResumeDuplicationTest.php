<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeDuplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);
    }

    public function test_guest_cannot_duplicate(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Original',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $this->post(route('resumes.duplicate', $resume))->assertRedirect(route('login'));
    }

    public function test_duplicate_creates_independent_copy_with_same_selections(): void
    {
        $user = User::factory()->create();
        $template = Template::first();
        $theme = Theme::first();

        $resume = $user->resumes()->create([
            'name' => 'Original Resume',
            'target_role' => 'Backend Dev',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
            'photo_enabled' => false,
            'photo_style' => 'square',
            'layout' => 'two_column',
            'ats_mode' => true,
            'page_size' => 'letter',
        ]);

        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Acme', 'start_date' => '2020-01-01', 'is_current' => true]);
        $ach = $exp->achievements()->create(['content' => 'Did X', 'sort_order' => 0]);
        $skill = $user->skills()->create(['name' => 'PHP', 'category' => 'technical']);
        $edu = $user->educations()->create(['institution' => 'Uni', 'qualification' => 'BSc', 'start_date' => '2015-01-01', 'is_current' => false, 'end_date' => '2019-01-01']);

        // Sections with explicit sort_order
        $expSection = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $skillSection = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => false, 'sort_order' => 2]);
        $eduSection = $resume->sections()->create(['section_type' => 'education', 'is_visible' => true, 'sort_order' => 1]);

        $expSection->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 1]);
        $expSection->items()->create(['itemable_type' => \App\Models\ExperienceAchievement::class, 'itemable_id' => $ach->id, 'sort_order' => 0]);
        $skillSection->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $skill->id, 'sort_order' => 0]);
        $eduSection->items()->create(['itemable_type' => \App\Models\Education::class, 'itemable_id' => $edu->id, 'sort_order' => 0]);

        $response = $this->actingAs($user)->post(route('resumes.duplicate', $resume));
        $response->assertRedirect();
        $this->assertDatabaseCount('resumes', 2);

        $copy = $user->resumes()->where('name', 'Copy of Original Resume')->first();
        $this->assertNotNull($copy);
        $this->assertNotEquals($resume->id, $copy->id);
        $this->assertEquals($user->id, $copy->user_id);

        // Preserve style/template/theme
        $this->assertEquals($resume->target_role, $copy->target_role);
        $this->assertEquals($resume->template_id, $copy->template_id);
        $this->assertEquals($resume->theme_id, $copy->theme_id);
        $this->assertEquals($resume->photo_enabled, $copy->photo_enabled);
        $this->assertEquals($resume->photo_style, $copy->photo_style);
        $this->assertEquals($resume->layout, $copy->layout);
        $this->assertEquals($resume->ats_mode, $copy->ats_mode);
        $this->assertEquals($resume->page_size, $copy->page_size);

        // Sections independent rows, same count and order
        $this->assertCount(3, $copy->sections);
        $this->assertNotEquals(
            $resume->sections()->pluck('id')->sort()->values()->toArray(),
            $copy->sections()->pluck('id')->sort()->values()->toArray()
        );
        // Check ordering preserved
        $originalOrder = $resume->sections()->orderBy('sort_order')->pluck('section_type')->toArray();
        $copyOrder = $copy->sections()->orderBy('sort_order')->pluck('section_type')->toArray();
        $this->assertEquals($originalOrder, $copyOrder);
        // Visibility preserved
        $this->assertEquals(
            $resume->sections()->where('section_type', 'skills')->first()->is_visible,
            $copy->sections()->where('section_type', 'skills')->first()->is_visible
        );

        // Items independent rows, same selections/order, same master refs (not duplicated masters)
        $this->assertEquals($resume->sections()->withCount('items')->get()->sum('items_count'), $copy->sections()->withCount('items')->get()->sum('items_count'));
        $this->assertCount(4, \App\Models\ResumeItem::whereIn('resume_section_id', $copy->sections()->pluck('id'))->get());

        // Verify items point to same master records
        $originalItems = $resume->sections()->with('items')->get()->flatMap->items->map(fn($i) => $i->itemable_type . ':' . $i->itemable_id)->sort()->values();
        $copyItems = $copy->sections()->with('items')->get()->flatMap->items->map(fn($i) => $i->itemable_type . ':' . $i->itemable_id)->sort()->values();
        $this->assertEquals($originalItems->toArray(), $copyItems->toArray());

        // IDs are different
        $origIds = $resume->sections()->with('items')->get()->flatMap->items->pluck('id')->sort()->values();
        $copyIds = $copy->sections()->with('items')->get()->flatMap->items->pluck('id')->sort()->values();
        $this->assertEmpty(array_intersect($origIds->toArray(), $copyIds->toArray()));

        // Master data not duplicated
        $this->assertEquals(1, \App\Models\Experience::count());
        $this->assertEquals(1, \App\Models\Skill::count());
        $this->assertEquals(1, \App\Models\Education::count());
    }

    public function test_editing_duplicate_does_not_change_original(): void
    {
        $user = User::factory()->create();
        $template = Template::first();
        $theme = Theme::first();
        $otherTheme = Theme::first(); // same for simplicity, but we test name change

        $resume = $user->resumes()->create([
            'name' => 'Original',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);
        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $section = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);

        $this->actingAs($user)->post(route('resumes.duplicate', $resume));
        $copy = $user->resumes()->where('name', 'Copy of Original')->first();

        // Edit duplicate: rename and add another skill
        $skill = $user->skills()->create(['name' => 'NewSkill', 'category' => 'technical']);
        $this->actingAs($user)->put(route('resumes.update', $copy), [
            'name' => 'Copy Renamed',
            'template_id' => $template->id,
            'theme_id' => $theme->id,
        ]);

        $skillSection = $copy->sections()->firstOrCreate(['section_type' => 'skills'], ['is_visible' => true, 'sort_order' => 1]);
        $this->actingAs($user)->post(route('resumes.items.store', $copy), [
            'section_type' => 'skills',
            'itemable_type' => \App\Models\Skill::class,
            'itemable_id' => $skill->id,
        ]);

        // Original unchanged
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'name' => 'Original']);
        $this->assertDatabaseHas('resumes', ['id' => $copy->id, 'name' => 'Copy Renamed']);
        $this->assertEquals(1, $resume->fresh()->sections()->withCount('items')->get()->sum('items_count'));
        $this->assertEquals(2, $copy->fresh()->sections()->withCount('items')->get()->sum('items_count'));
    }

    public function test_unauthorized_user_cannot_duplicate_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $resume = $owner->resumes()->create([
            'name' => 'Owner Resume',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $this->actingAs($attacker)->post(route('resumes.duplicate', $resume))->assertStatus(403);
        $this->assertDatabaseCount('resumes', 1);
    }

    public function test_duplicating_nonexistent_returns_404(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('resumes.duplicate', 9999))->assertStatus(404);
    }

    public function test_duplicate_preserves_section_and_item_order(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Order Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        // Create 3 skills and attach in specific order
        $s1 = $user->skills()->create(['name' => 'A', 'category' => 'technical']);
        $s2 = $user->skills()->create(['name' => 'B', 'category' => 'technical']);
        $s3 = $user->skills()->create(['name' => 'C', 'category' => 'technical']);

        $section = $resume->sections()->create(['section_type' => 'skills', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $s2->id, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $s1->id, 'sort_order' => 1]);
        $section->items()->create(['itemable_type' => \App\Models\Skill::class, 'itemable_id' => $s3->id, 'sort_order' => 2]);

        $this->actingAs($user)->post(route('resumes.duplicate', $resume));
        $copy = $user->resumes()->where('name', 'Copy of Order Test')->first();
        $copySection = $copy->sections()->where('section_type', 'skills')->first();
        $order = $copySection->items()->orderBy('sort_order')->get()->pluck('itemable_id')->toArray();
        $this->assertEquals([$s2->id, $s1->id, $s3->id], $order);
    }

    public function test_duplicate_does_not_duplicate_master_data(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Master Check',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $exp = $user->experiences()->create(['job_title' => 'Dev', 'organization' => 'Org', 'start_date' => '2020-01-01', 'is_current' => true]);
        $section = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);

        $expCountBefore = \App\Models\Experience::count();
        $achCountBefore = \App\Models\ExperienceAchievement::count();
        $skillCountBefore = \App\Models\Skill::count();

        $this->actingAs($user)->post(route('resumes.duplicate', $resume));

        $this->assertEquals($expCountBefore, \App\Models\Experience::count());
        $this->assertEquals($achCountBefore, \App\Models\ExperienceAchievement::count());
        $this->assertEquals($skillCountBefore, \App\Models\Skill::count());
    }
}
