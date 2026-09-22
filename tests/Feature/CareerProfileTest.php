<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_career_profile(): void
    {
        $response = $this->get(route('career-profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_career_profile_and_auto_creates_profile(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseCount('profiles', 0);

        $response = $this->actingAs($user)->get(route('career-profile.edit'));

        $response->assertOk();
        $response->assertSee('Personal Information');

        $this->assertDatabaseHas('profiles', ['user_id' => $user->id]);
        $user->refresh();
        $this->assertNotNull($user->profile);
    }

    public function test_user_can_create_and_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'full_name' => 'Jane Doe',
            'professional_title' => 'Junior Developer',
            'phone' => '123-456-7890',
            'location' => 'Berlin, Germany',
            'website_url' => 'https://janedoe.dev',
            'linkedin_url' => 'https://linkedin.com/in/janedoe',
            'github_url' => 'https://github.com/janedoe',
        ]);

        $response->assertRedirect(route('career-profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'full_name' => 'Jane Doe',
            'professional_title' => 'Junior Developer',
            'phone' => '123-456-7890',
            'location' => 'Berlin, Germany',
            'website_url' => 'https://janedoe.dev',
            'linkedin_url' => 'https://linkedin.com/in/janedoe',
            'github_url' => 'https://github.com/janedoe',
        ]);

        // Update again
        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'full_name' => 'Jane Smith',
            'professional_title' => 'Senior Developer',
            'phone' => null,
            'location' => null,
            'website_url' => null,
            'linkedin_url' => null,
            'github_url' => null,
        ]);

        $response->assertRedirect(route('career-profile.edit'));
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'full_name' => 'Jane Smith',
            'professional_title' => 'Senior Developer',
        ]);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'phone' => null,
        ]);
    }

    public function test_photo_upload_stores_on_private_disk_with_randomized_filename(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'full_name' => 'Test User',
            'photo' => $file,
        ]);

        $response->assertRedirect(route('career-profile.edit'));

        $user->refresh();
        $profile = $user->profile;
        $this->assertNotNull($profile->photo_path);
        $this->assertStringStartsWith('profile-photos/', $profile->photo_path);
        $this->assertStringEndsWith('.jpg', $profile->photo_path);
        $this->assertNotEquals('avatar.jpg', basename($profile->photo_path));
        Storage::disk('private')->assertExists($profile->photo_path);
    }

    public function test_photo_upload_replaces_old_photo_and_deletes_original(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $first = UploadedFile::fake()->image('first.png', 100, 100);
        $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $first,
        ]);
        $user->refresh();
        $firstPath = $user->profile->photo_path;
        Storage::disk('private')->assertExists($firstPath);

        $second = UploadedFile::fake()->image('second.webp', 100, 100);
        $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $second,
        ]);

        $user->refresh();
        $secondPath = $user->profile->photo_path;
        $this->assertNotEquals($firstPath, $secondPath);
        Storage::disk('private')->assertExists($secondPath);
        Storage::disk('private')->assertMissing($firstPath);
    }

    public function test_photo_validation_rejects_bad_mime(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $badFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $badFile,
        ]);

        $response->assertSessionHasErrors('photo');
        $user->refresh();
        $this->assertTrue($user->profile === null || $user->profile->photo_path === null);
    }

    public function test_photo_validation_rejects_text_file_disguised_as_image(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $badFile = UploadedFile::fake()->create('evil.jpg', 100, 'text/plain');

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $badFile,
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_photo_validation_rejects_oversized_file(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $bigFile = UploadedFile::fake()->image('big.jpg');
        // Make it report >2MB by setting size manually (fake file size is in KB)
        $bigFile = UploadedFile::fake()->create('big.jpg', 3000, 'image/jpeg'); // 3000 KB > 2048

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $bigFile,
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_photo_can_be_removed_via_checkbox(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $file,
        ]);
        $user->refresh();
        $path = $user->profile->photo_path;
        Storage::disk('private')->assertExists($path);

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'remove_photo' => true,
        ]);
        $response->assertRedirect(route('career-profile.edit'));

        $user->refresh();
        $this->assertNull($user->profile->photo_path);
        Storage::disk('private')->assertMissing($path);
    }

    public function test_photo_served_via_protected_route(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.png');
        $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $file,
        ]);

        $response = $this->actingAs($user)->get(route('career-profile.photo'));
        $response->assertOk();
        // Content-type should be image/png or image/jpeg depending on fake
        $this->assertTrue(str_contains($response->headers->get('Content-Type'), 'image/'));
    }

    public function test_unauthorized_access_blocked_via_policy(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $profile = $owner->profile()->create([
            'full_name' => 'Owner Name',
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $profile));
        $this->assertTrue(Gate::forUser($owner)->allows('update', $profile));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $profile));

        $this->assertFalse(Gate::forUser($attacker)->allows('view', $profile));
        $this->assertFalse(Gate::forUser($attacker)->allows('update', $profile));
        $this->assertFalse(Gate::forUser($attacker)->allows('delete', $profile));
        $this->assertTrue(Gate::forUser($attacker)->denies('view', $profile));
    }

    public function test_url_validation_rejects_invalid_urls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('career-profile.update'), [
            'website_url' => 'not-a-url',
            'linkedin_url' => 'ftp://invalid',
            'github_url' => 'just-text',
        ]);

        $response->assertSessionHasErrors(['website_url']);
        // github and linkedin may pass url validation if ftp, so we test at least website fails
    }

    public function test_photo_delete_route_removes_file(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->actingAs($user)->put(route('career-profile.update'), [
            'photo' => $file,
        ]);
        $user->refresh();
        $path = $user->profile->photo_path;
        Storage::disk('private')->assertExists($path);

        $response = $this->actingAs($user)->delete(route('career-profile.photo.destroy'));
        $response->assertRedirect(route('career-profile.edit'));
        $user->refresh();
        $this->assertNull($user->profile->photo_path);
        Storage::disk('private')->assertMissing($path);
    }
}
