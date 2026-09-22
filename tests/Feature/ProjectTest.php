<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
        $this->get(route('projects.create'))->assertRedirect(route('login'));
        $this->post(route('projects.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_create_project_with_technologies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'CareerForge',
            'description' => 'Resume builder',
            'role' => 'Lead Developer',
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31',
            'project_url' => 'https://careerforge.example.com',
            'github_url' => 'https://github.com/example/careerforge',
            'technologies' => [
                ['name' => 'Laravel', 'sort_order' => 0],
                ['name' => 'Vue', 'sort_order' => 1],
            ],
        ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'CareerForge',
            'role' => 'Lead Developer',
        ]);
        $project = Project::where('name', 'CareerForge')->first();
        $this->assertCount(2, $project->technologies);
        $this->assertEquals(['Laravel', 'Vue'], $project->technologies->pluck('name')->toArray());
    }

    public function test_project_validation_requires_name(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => '',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_project_url_and_github_url_must_be_valid_urls(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Test',
            'project_url' => 'not-a-url',
            'github_url' => 'also-not-url',
        ]);
        $response->assertSessionHasErrors(['project_url', 'github_url']);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Test',
            'start_date' => '2024-01-01',
            'end_date' => '2023-01-01',
        ]);
        $response->assertSessionHasErrors('end_date');
    }

    public function test_technologies_ordering_by_sort_order(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'Proj', 'start_date' => '2023-01-01']);
        $project->technologies()->create(['name' => 'B', 'sort_order' => 1]);
        $project->technologies()->create(['name' => 'A', 'sort_order' => 0]);
        $project->technologies()->create(['name' => 'C', 'sort_order' => 2]);

        $ordered = $project->fresh()->technologies;
        $this->assertEquals(['A', 'B', 'C'], $ordered->pluck('name')->toArray());
    }

    public function test_user_can_update_project_and_manage_technologies(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'Old', 'description' => 'Old desc']);
        $tech1 = $project->technologies()->create(['name' => 'PHP', 'sort_order' => 0]);
        $tech2 = $project->technologies()->create(['name' => 'JS', 'sort_order' => 1]);

        $response = $this->actingAs($user)->put(route('projects.update', $project), [
            'name' => 'New',
            'description' => 'New desc',
            'technologies' => [
                ['id' => $tech1->id, 'name' => 'PHP 8', 'sort_order' => 0],
                ['id' => $tech2->id, '_delete' => true],
                ['name' => 'Laravel', 'sort_order' => 2],
            ],
        ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'New']);
        $this->assertDatabaseHas('project_technologies', ['id' => $tech1->id, 'name' => 'PHP 8']);
        $this->assertDatabaseMissing('project_technologies', ['id' => $tech2->id]);
        $this->assertDatabaseHas('project_technologies', ['project_id' => $project->id, 'name' => 'Laravel']);
        $this->assertEquals(2, $project->fresh()->technologies()->count());
    }

    public function test_user_can_delete_project_cascades_technologies(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'ToDelete']);
        $tech = $project->technologies()->create(['name' => 'Tech', 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete(route('projects.destroy', $project));
        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_technologies', ['id' => $tech->id]);
    }

    public function test_unauthorized_user_cannot_access_another_users_project(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $project = $owner->projects()->create(['name' => 'Secret']);
        $tech = $project->technologies()->create(['name' => 'SecretTech', 'sort_order' => 0]);

        $this->actingAs($attacker)->get(route('projects.edit', $project))->assertStatus(403);
        $this->actingAs($attacker)->put(route('projects.update', $project), ['name' => 'Hacked'])->assertStatus(403);
        $this->actingAs($attacker)->delete(route('projects.destroy', $project))->assertStatus(403);

        // Technologies are via project, so check that updating via project with attacker's tech delete is blocked via policy indirectly - but direct tech policy
        // We test that project technologies are also protected via project ownership in controller authorize
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Secret']);
        $this->assertDatabaseHas('project_technologies', ['id' => $tech->id]);
    }

    public function test_projects_index_only_shows_own(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->projects()->create(['name' => 'Mine']);
        $other->projects()->create(['name' => 'Other']);

        $response = $this->actingAs($user)->get(route('projects.index'));
        $response->assertSee('Mine')->assertDontSee('Other');
    }

    public function test_user_can_build_full_history_multiple_projects(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 2; $i++) {
            $this->actingAs($user)->post(route('projects.store'), [
                'name' => "Project $i",
                'technologies' => [
                    ['name' => "Tech $i-A", 'sort_order' => 0],
                    ['name' => "Tech $i-B", 'sort_order' => 1],
                ],
            ])->assertRedirect(route('projects.index'));
        }
        $this->assertDatabaseCount('projects', 2);
        $this->assertDatabaseCount('project_technologies', 4);
    }
}
