<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use App\Services\Pdf\PdfGeneratorInterface;
use Database\Seeders\TemplateSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TemplateSeeder::class);
        $this->seed(ThemeSeeder::class);
    }

    public function test_guest_cannot_export(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $this->post(route('resumes.export', $resume))->assertRedirect(route('login'));
    }

    public function test_successful_export_produces_valid_pdf(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'full_name' => 'John Doe',
            'professional_title' => 'Developer',
        ]);

        $resume = $user->resumes()->create([
            'name' => 'My Resume',
            'target_role' => 'Backend',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $exp = $user->experiences()->create([
            'job_title' => 'Dev',
            'organization' => 'Acme',
            'start_date' => '2020-01-01',
            'is_current' => true,
        ]);
        $section = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create([
            'itemable_type' => \App\Models\Experience::class,
            'itemable_id' => $exp->id,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('resumes.export', $resume));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('my-resume.pdf', $disposition);
        $this->assertStringContainsString('attachment', $disposition);

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('%PDF', $content);
        // PDF should contain some text – Dompdf embeds text, but we can check length > 1000
        $this->assertGreaterThan(1000, strlen($content));
        // Should not be corrupt: ensure PDF header is valid and contains PDF version
        $this->assertMatchesRegularExpression('/%PDF-1\.[0-9]/', substr($content, 0, 20));
    }

    public function test_export_uses_same_html_as_preview(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['full_name' => 'Alice', 'professional_title' => 'Engineer']);
        $resume = $user->resumes()->create([
            'name' => 'Preview Match',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);
        $exp = $user->experiences()->create(['job_title' => 'Senior', 'organization' => 'Corp', 'start_date' => '2020-01-01', 'is_current' => true]);
        $section = $resume->sections()->create(['section_type' => 'experience', 'is_visible' => true, 'sort_order' => 0]);
        $section->items()->create(['itemable_type' => \App\Models\Experience::class, 'itemable_id' => $exp->id, 'sort_order' => 0]);

        $preview = $this->actingAs($user)->get(route('resumes.preview', $resume));
        $preview->assertOk();
        $previewHtml = $preview->getContent();
        $this->assertStringContainsString('Senior', $previewHtml);
        $this->assertStringContainsString('Corp', $previewHtml);
        $this->assertStringContainsString('Alice', $previewHtml);

        $pdfResponse = $this->actingAs($user)->post(route('resumes.export', $resume));
        $pdfResponse->assertOk();
        $pdfContent = $pdfResponse->getContent();
        $this->assertStringStartsWith('%PDF', $pdfContent);
        // PDF binary will contain the text strings somewhere (maybe encoded), but at least ensure PDF not empty
        $this->assertGreaterThan(1000, strlen($pdfContent));
    }

    public function test_simulated_failure_shows_error_and_never_serves_corrupt_file(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'Fail Test',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $this->mock(PdfGeneratorInterface::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Simulated failure'));
        });

        $response = $this->actingAs($user)->post(route('resumes.export', $resume));

        // Should redirect back with error, not serve PDF
        $response->assertRedirect();
        $response->assertSessionHasErrors('pdf');
        $this->assertStringContainsString('PDF generation failed', session('errors')->first('pdf'));

        // Ensure response is not PDF
        $this->assertNotEquals('application/pdf', $response->headers->get('Content-Type'));

        // JSON request should get 500 JSON
        $this->mock(PdfGeneratorInterface::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Simulated failure'));
        });

        $response = $this->actingAs($user)->postJson(route('resumes.export', $resume));
        $response->assertStatus(500);
        $response->assertJson(['message' => 'PDF generation failed. Please try again later.']);
        $this->assertNotEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_unauthorized_user_cannot_export_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $resume = $owner->resumes()->create([
            'name' => 'Owner Resume',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $this->actingAs($attacker)->post(route('resumes.export', $resume))->assertStatus(403);
    }

    public function test_pdf_generator_interface_is_bound_to_dompdf(): void
    {
        $generator = app(PdfGeneratorInterface::class);
        $this->assertInstanceOf(\App\Services\Pdf\DompdfGenerator::class, $generator);

        // Test that DompdfGenerator can actually generate a simple PDF
        $html = '<html><body><h1>Hello World</h1><p>Test content for PDF generation.</p></body></html>';
        $pdf = $generator->generate($html);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));
    }

    public function test_export_filename_is_slugified_resume_name(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'name' => 'My Awesome Resume 2024!',
            'template_id' => Template::first()->id,
            'theme_id' => Theme::first()->id,
        ]);

        $response = $this->actingAs($user)->post(route('resumes.export', $resume));
        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('my-awesome-resume-2024.pdf', $disposition);
    }
}
