<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\ExperienceAchievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_experiences(): void
    {
        $this->get(route('experiences.index'))->assertRedirect(route('login'));
        $this->get(route('experiences.create'))->assertRedirect(route('login'));
        $this->post(route('experiences.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_create_experience_with_achievements(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'Software Engineer',
            'organization' => 'Acme Corp',
            'location' => 'Berlin',
            'start_date' => '2022-01-15',
            'end_date' => '2023-01-15',
            'is_current' => false,
            'description' => 'Built cool stuff',
            'achievements' => [
                ['content' => 'Shipped feature X', 'sort_order' => 0],
                ['content' => 'Improved performance 30%', 'sort_order' => 1],
            ],
        ]);

        $response->assertRedirect(route('experiences.index'));
        $this->assertDatabaseHas('experiences', [
            'user_id' => $user->id,
            'job_title' => 'Software Engineer',
            'organization' => 'Acme Corp',
            'location' => 'Berlin',
            'is_current' => 0,
        ]);
        $this->assertDatabaseCount('experience_achievements', 2);
        $exp = Experience::where('user_id', $user->id)->first();
        $this->assertCount(2, $exp->achievements);
        $this->assertEquals('Shipped feature X', $exp->achievements->first()->content);
    }

    public function test_user_can_view_experiences_index(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $exp1 = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org1',
            'start_date' => '2022-01-01',
            'is_current' => false,
            'end_date' => '2022-12-31',
        ]);
        $other->experiences()->create([
            'job_title' => 'Other',
            'organization' => 'OtherOrg',
            'start_date' => '2021-01-01',
            'is_current' => true,
        ]);

        $response = $this->actingAs($user)->get(route('experiences.index'));
        $response->assertOk();
        $response->assertSee('Dev');
        $response->assertDontSee('Other');
    }

    public function test_experience_validation_requires_job_title_and_organization(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => '',
            'organization' => '',
            'start_date' => '2022-01-01',
            'end_date' => '2022-12-31',
        ]);

        $response->assertSessionHasErrors(['job_title', 'organization']);
    }

    public function test_current_flag_validation_requires_end_date_unless_current(): void
    {
        $user = User::factory()->create();

        // Not current and missing end_date -> should error
        $response = $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2022-01-01',
            'is_current' => false,
            // end_date missing
        ]);
        $response->assertSessionHasErrors('end_date');

        // Current true and missing end_date -> should pass and null end_date
        $response = $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'Dev2',
            'organization' => 'Org2',
            'start_date' => '2022-01-01',
            'is_current' => true,
        ]);
        $response->assertRedirect(route('experiences.index'));
        $this->assertDatabaseHas('experiences', [
            'job_title' => 'Dev2',
            'is_current' => 1,
            'end_date' => null,
        ]);

        // Current true with end_date provided -> should be nulled
        $response = $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'Dev3',
            'organization' => 'Org3',
            'start_date' => '2022-01-01',
            'end_date' => '2023-01-01',
            'is_current' => true,
        ]);
        $response->assertRedirect(route('experiences.index'));
        $exp = Experience::where('job_title', 'Dev3')->first();
        $this->assertNull($exp->end_date);
        $this->assertTrue($exp->is_current);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2023-01-01',
            'end_date' => '2022-01-01',
            'is_current' => false,
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_user_can_update_experience(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create([
            'job_title' => 'Old',
            'organization' => 'OldOrg',
            'start_date' => '2020-01-01',
            'end_date' => '2020-12-31',
            'is_current' => false,
        ]);

        $response = $this->actingAs($user)->put(route('experiences.update', $exp), [
            'job_title' => 'Updated',
            'organization' => 'NewOrg',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'is_current' => false,
        ]);

        $response->assertRedirect(route('experiences.index'));
        $this->assertDatabaseHas('experiences', [
            'id' => $exp->id,
            'job_title' => 'Updated',
            'organization' => 'NewOrg',
        ]);
    }

    public function test_update_with_current_flag_clears_end_date(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'is_current' => false,
        ]);

        $response = $this->actingAs($user)->put(route('experiences.update', $exp), [
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);

        $response->assertRedirect(route('experiences.index'));
        $exp->refresh();
        $this->assertTrue($exp->is_current);
        $this->assertNull($exp->end_date);
    }

    public function test_user_can_delete_experience_and_cascades_achievements(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
        ]);
        $ach = $exp->achievements()->create(['content' => 'Did thing', 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete(route('experiences.destroy', $exp));
        $response->assertRedirect(route('experiences.index'));
        $this->assertDatabaseMissing('experiences', ['id' => $exp->id]);
        $this->assertDatabaseMissing('experience_achievements', ['id' => $ach->id]);
    }

    public function test_achievement_crud_via_nested_and_shallow_routes(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);

        // Store via shallow nested route
        $response = $this->actingAs($user)->post(route('experiences.achievements.store', $exp), [
            'content' => 'First achievement',
            'sort_order' => 5,
        ]);
        $response->assertRedirect(route('experiences.edit', $exp));
        $this->assertDatabaseHas('experience_achievements', [
            'experience_id' => $exp->id,
            'content' => 'First achievement',
            'sort_order' => 5,
        ]);

        $ach = $exp->achievements()->first();

        // Update via shallow
        $response = $this->actingAs($user)->put(route('achievements.update', $ach), [
            'content' => 'Updated content',
            'sort_order' => 10,
        ]);
        $response->assertRedirect(route('experiences.edit', $exp));
        $this->assertDatabaseHas('experience_achievements', [
            'id' => $ach->id,
            'content' => 'Updated content',
            'sort_order' => 10,
        ]);

        // Delete via shallow
        $response = $this->actingAs($user)->delete(route('achievements.destroy', $ach));
        $response->assertRedirect(route('experiences.edit', $exp));
        $this->assertDatabaseMissing('experience_achievements', ['id' => $ach->id]);
    }

    public function test_achievement_ordering_by_sort_order(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);

        $exp->achievements()->create(['content' => 'Second', 'sort_order' => 1]);
        $exp->achievements()->create(['content' => 'First', 'sort_order' => 0]);
        $exp->achievements()->create(['content' => 'Third', 'sort_order' => 2]);

        $ordered = $exp->fresh()->achievements; // should be ordered by sort_order via relation
        $this->assertEquals(['First', 'Second', 'Third'], $ordered->pluck('content')->toArray());

        // Update sort_order to reorder
        $first = $exp->achievements()->where('content', 'First')->first();
        $this->actingAs($user)->put(route('achievements.update', $first), [
            'content' => 'First',
            'sort_order' => 5,
        ]);
        $ordered = $exp->fresh()->achievements;
        $this->assertEquals(['Second', 'Third', 'First'], $ordered->pluck('content')->toArray());
    }

    public function test_achievement_validation_requires_content(): void
    {
        $user = User::factory()->create();
        $exp = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);

        $response = $this->actingAs($user)->post(route('experiences.achievements.store', $exp), [
            'content' => '',
        ]);
        $response->assertSessionHasErrors('content');
    }

    public function test_unauthorized_user_cannot_access_another_users_experience(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $exp = $owner->experiences()->create([
            'job_title' => 'Secret',
            'organization' => 'SecretOrg',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);

        $this->actingAs($attacker)->get(route('experiences.edit', $exp))->assertStatus(403);
        $this->actingAs($attacker)->put(route('experiences.update', $exp), [
            'job_title' => 'Hacked',
            'organization' => 'Hacked',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ])->assertStatus(403);
        $this->actingAs($attacker)->delete(route('experiences.destroy', $exp))->assertStatus(403);

        $this->assertDatabaseHas('experiences', ['id' => $exp->id, 'job_title' => 'Secret']);
    }

    public function test_unauthorized_user_cannot_touch_another_users_achievement(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $exp = $owner->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Org',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);
        $ach = $exp->achievements()->create(['content' => 'Secret ach', 'sort_order' => 0]);

        $this->actingAs($attacker)->put(route('achievements.update', $ach), [
            'content' => 'Hacked',
        ])->assertStatus(403);

        $this->actingAs($attacker)->delete(route('achievements.destroy', $ach))->assertStatus(403);

        $this->actingAs($attacker)->post(route('experiences.achievements.store', $exp), [
            'content' => 'Hacked new',
        ])->assertStatus(403);

        $this->assertDatabaseHas('experience_achievements', ['id' => $ach->id, 'content' => 'Secret ach']);
    }

    public function test_user_can_build_full_history_multiple_experiences(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($user)->post(route('experiences.store'), [
                'job_title' => "Job $i",
                'organization' => "Org $i",
                'start_date' => "202{$i}-01-01",
                'end_date' => "202{$i}-12-31",
                'is_current' => false,
                'achievements' => [
                    ['content' => "Ach $i-1", 'sort_order' => 0],
                    ['content' => "Ach $i-2", 'sort_order' => 1],
                ],
            ])->assertRedirect(route('experiences.index'));
        }

        $this->assertCount(3, $user->fresh()->experiences);
        $this->assertDatabaseCount('experience_achievements', 6);
        $response = $this->actingAs($user)->get(route('experiences.index'));
        $response->assertSee('Job 1')->assertSee('Job 2')->assertSee('Job 3');
        $response->assertSee('Ach 1-1')->assertSee('Ach 2-2');
    }

    public function test_second_experience_with_blank_achievements_via_ui_succeeds(): void
    {
        $user = User::factory()->create();

        // First experience via UI form (3 inputs, one blank)
        $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'First',
            'organization' => 'Org1',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'is_current' => false,
            'achievements' => [
                ['content' => 'A1', 'sort_order' => 0],
                ['content' => '', 'sort_order' => 1],
                ['content' => '', 'sort_order' => 2],
            ],
        ])->assertRedirect(route('experiences.index'));
        $this->assertDatabaseCount('experiences', 1);
        $this->assertDatabaseCount('experience_achievements', 1);

        // Second experience same UI pattern – should succeed and not alter first
        $first = $user->fresh()->experiences()->first();
        $firstDataBefore = $first->fresh()->toArray();

        $this->actingAs($user)->post(route('experiences.store'), [
            'job_title' => 'Second',
            'organization' => 'Org2',
            'start_date' => '2022-01-01',
            'end_date' => '2023-01-01',
            'is_current' => false,
            'achievements' => [
                ['content' => '', 'sort_order' => 0],
                ['content' => '', 'sort_order' => 1],
                ['content' => '', 'sort_order' => 2],
            ],
        ])->assertRedirect(route('experiences.index'));

        $this->assertDatabaseCount('experiences', 2);
        $this->assertDatabaseHas('experiences', ['job_title' => 'First']);
        $this->assertDatabaseHas('experiences', ['job_title' => 'Second']);
        $this->assertEquals($firstDataBefore['job_title'], $first->fresh()->job_title);
    }
}
