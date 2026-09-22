<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_skills(): void
    {
        $this->get(route('skills.index'))->assertRedirect(route('login'));
        $this->get(route('skills.create'))->assertRedirect(route('login'));
        $this->post(route('skills.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_create_skill(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('skills.store'), [
            'name' => 'Laravel',
            'category' => 'technical',
            'proficiency' => 4,
        ]);
        $response->assertRedirect(route('skills.index'));
        $this->assertDatabaseHas('skills', [
            'user_id' => $user->id,
            'name' => 'Laravel',
            'category' => 'technical',
            'proficiency' => 4,
        ]);
    }

    public function test_skill_validation_requires_name_and_valid_category(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('skills.store'), [
            'name' => '',
            'category' => 'invalid_category',
        ]);
        $response->assertSessionHasErrors(['name', 'category']);

        $response = $this->actingAs($user)->post(route('skills.store'), [
            'name' => 'PHP',
            'category' => 'technical',
            'proficiency' => 6,
        ]);
        $response->assertSessionHasErrors('proficiency');

        $response = $this->actingAs($user)->post(route('skills.store'), [
            'name' => 'PHP',
            'category' => 'technical',
            'proficiency' => 0,
        ]);
        $response->assertSessionHasErrors('proficiency');
    }

    public function test_proficiency_is_optional(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('skills.store'), [
            'name' => 'Python',
            'category' => 'it',
        ]);
        $response->assertRedirect(route('skills.index'));
        $this->assertDatabaseHas('skills', ['name' => 'Python', 'proficiency' => null]);
    }

    public function test_category_enum_accepts_all_valid_values(): void
    {
        $user = User::factory()->create();
        $categories = ['technical','it','software_tools','soft','digital_media','leadership','other'];
        foreach ($categories as $cat) {
            $response = $this->actingAs($user)->post(route('skills.store'), [
                'name' => "Skill $cat",
                'category' => $cat,
            ]);
            $response->assertRedirect(route('skills.index'));
            $this->assertDatabaseHas('skills', ['name' => "Skill $cat", 'category' => $cat]);
        }
    }

    public function test_user_can_update_and_delete_skill(): void
    {
        $user = User::factory()->create();
        $skill = $user->skills()->create(['name' => 'Old', 'category' => 'technical']);

        $response = $this->actingAs($user)->put(route('skills.update', $skill), [
            'name' => 'New',
            'category' => 'soft',
            'proficiency' => 3,
        ]);
        $response->assertRedirect(route('skills.index'));
        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'name' => 'New', 'category' => 'soft']);

        $response = $this->actingAs($user)->delete(route('skills.destroy', $skill));
        $response->assertRedirect(route('skills.index'));
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_unauthorized_user_cannot_access_another_users_skill(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $skill = $owner->skills()->create(['name' => 'Secret', 'category' => 'other']);

        $this->actingAs($attacker)->get(route('skills.edit', $skill))->assertStatus(403);
        $this->actingAs($attacker)->put(route('skills.update', $skill), [
            'name' => 'Hacked',
            'category' => 'technical',
        ])->assertStatus(403);
        $this->actingAs($attacker)->delete(route('skills.destroy', $skill))->assertStatus(403);
        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'name' => 'Secret']);
    }

    public function test_skills_index_grouped_and_only_shows_own(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->skills()->create(['name' => 'MySkill', 'category' => 'technical']);
        $other->skills()->create(['name' => 'OtherSkill', 'category' => 'technical']);

        $response = $this->actingAs($user)->get(route('skills.index'));
        $response->assertSee('MySkill')->assertDontSee('OtherSkill');
    }
}
