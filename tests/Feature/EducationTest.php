<?php

namespace Tests\Feature;

use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_educations(): void
    {
        $this->get(route('educations.index'))->assertRedirect(route('login'));
        $this->get(route('educations.create'))->assertRedirect(route('login'));
        $this->post(route('educations.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_create_and_view_education(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('educations.store'), [
            'institution' => 'MIT',
            'qualification' => 'BSc Computer Science',
            'field_of_study' => 'AI',
            'start_date' => '2018-09-01',
            'end_date' => '2022-06-15',
            'is_current' => false,
            'description' => 'Studied AI',
        ]);

        $response->assertRedirect(route('educations.index'));
        $this->assertDatabaseHas('educations', [
            'user_id' => $user->id,
            'institution' => 'MIT',
            'qualification' => 'BSc Computer Science',
        ]);

        $response = $this->actingAs($user)->get(route('educations.index'));
        $response->assertOk()->assertSee('MIT')->assertSee('BSc Computer Science');
    }

    public function test_education_validation_requires_institution_and_qualification(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('educations.store'), [
            'institution' => '',
            'qualification' => '',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
        ]);
        $response->assertSessionHasErrors(['institution', 'qualification']);
    }

    public function test_is_current_requires_end_date_unless_current(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('educations.store'), [
            'institution' => 'Uni',
            'qualification' => 'Degree',
            'start_date' => '2020-01-01',
            'is_current' => false,
        ]);
        $response->assertSessionHasErrors('end_date');

        $response = $this->actingAs($user)->post(route('educations.store'), [
            'institution' => 'Uni2',
            'qualification' => 'Degree2',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);
        $response->assertRedirect(route('educations.index'));
        $this->assertDatabaseHas('educations', ['qualification' => 'Degree2', 'is_current' => 1, 'end_date' => null]);
    }

    public function test_is_current_true_clears_end_date_even_if_provided(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('educations.store'), [
            'institution' => 'Uni',
            'qualification' => 'Degree',
            'start_date' => '2020-01-01',
            'end_date' => '2022-01-01',
            'is_current' => true,
        ]);

        $ed = Education::where('qualification', 'Degree')->first();
        $this->assertNull($ed->end_date);
        $this->assertTrue($ed->is_current);
    }

    public function test_user_can_update_and_delete_education(): void
    {
        $user = User::factory()->create();
        $ed = $user->educations()->create([
            'institution' => 'Old Uni',
            'qualification' => 'Old Degree',
            'start_date' => '2019-01-01',
            'end_date' => '2020-01-01',
        ]);

        $response = $this->actingAs($user)->put(route('educations.update', $ed), [
            'institution' => 'New Uni',
            'qualification' => 'New Degree',
            'start_date' => '2019-01-01',
            'end_date' => '2021-01-01',
        ]);
        $response->assertRedirect(route('educations.index'));
        $this->assertDatabaseHas('educations', ['id' => $ed->id, 'institution' => 'New Uni']);

        $response = $this->actingAs($user)->delete(route('educations.destroy', $ed));
        $response->assertRedirect(route('educations.index'));
        $this->assertDatabaseMissing('educations', ['id' => $ed->id]);
    }

    public function test_unauthorized_user_cannot_access_another_users_education(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $ed = $owner->educations()->create([
            'institution' => 'Secret',
            'qualification' => 'Secret Degree',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);

        $this->actingAs($attacker)->get(route('educations.edit', $ed))->assertStatus(403);
        $this->actingAs($attacker)->put(route('educations.update', $ed), [
            'institution' => 'Hacked',
            'qualification' => 'Hacked',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ])->assertStatus(403);
        $this->actingAs($attacker)->delete(route('educations.destroy', $ed))->assertStatus(403);
    }

    public function test_educations_index_only_shows_own_records(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->educations()->create(['institution' => 'Mine', 'qualification' => 'MyDegree', 'start_date' => '2020-01-01', 'is_current' => true]);
        $other->educations()->create(['institution' => 'Other', 'qualification' => 'OtherDegree', 'start_date' => '2020-01-01', 'is_current' => true]);

        $response = $this->actingAs($user)->get(route('educations.index'));
        $response->assertSee('Mine')->assertDontSee('Other');
    }
}
