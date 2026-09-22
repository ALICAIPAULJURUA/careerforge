<?php

namespace App\Http\Controllers;

use App\Actions\Resume\CreateResumeAction;
use App\Actions\Resume\DuplicateResumeAction;
use App\Http\Requests\StoreResumeRequest;
use App\Http\Requests\UpdateResumeRequest;
use App\Models\Resume;
use App\Models\Template;
use App\Models\Theme;
use App\Services\Pdf\PdfGeneratorInterface;
use App\Services\ResumeRendering\ResumeRenderingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResumeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Resume::class);
        $resumes = $request->user()->resumes()->with(['template', 'theme', 'sections'])->latest()->get();
        return view('resumes.index', compact('resumes'));
    }

    public function create()
    {
        $this->authorize('create', Resume::class);
        $templates = Template::where('is_active', true)->get();
        $themes = Theme::all();
        return view('resumes.create', compact('templates', 'themes'));
    }

    public function store(StoreResumeRequest $request, CreateResumeAction $action)
    {
        $this->authorize('create', Resume::class);
        $resume = $action($request->user(), $request->validated());
        return redirect()->route('resumes.edit', $resume)->with('status', 'Resume created.');
    }

    public function show(Resume $resume)
    {
        $this->authorize('view', $resume);
        $resume->load(['sections.items.itemable', 'template', 'theme']);
        return view('resumes.show', compact('resume'));
    }

    public function edit(Resume $resume)
    {
        $this->authorize('view', $resume);
        $resume->load(['sections.items.itemable', 'template', 'theme']);
        $templates = Template::where('is_active', true)->get();
        $themes = Theme::all();

        // Masters for picker
        $user = auth()->user();
        $experiences = $user->experiences()->with('achievements')->get();
        $educations = $user->educations()->get();
        $skills = $user->skills()->get();
        $projects = $user->projects()->with('technologies')->get();

        return view('resumes.edit', compact('resume', 'templates', 'themes', 'experiences', 'educations', 'skills', 'projects'));
    }

    public function update(UpdateResumeRequest $request, Resume $resume)
    {
        $this->authorize('update', $resume);
        $resume->update($request->validated());
        return redirect()->route('resumes.edit', $resume)->with('status', 'Resume updated.');
    }

    public function destroy(Resume $resume)
    {
        $this->authorize('delete', $resume);
        $resume->delete();
        return redirect()->route('resumes.index')->with('status', 'Resume deleted.');
    }

    public function duplicate(Resume $resume, DuplicateResumeAction $action)
    {
        $this->authorize('view', $resume);
        $copy = $action($resume);
        return redirect()->route('resumes.edit', $copy)->with('status', 'Resume duplicated.');
    }

    public function preview(Resume $resume, ResumeRenderingService $service)
    {
        $this->authorize('view', $resume);
        $renderable = $service->render($resume);

        $view = 'templates.' . ($resume->template?->key ?? 'modern');
        if (! view()->exists($view)) {
            $view = 'templates.modern';
        }

        return view($view, ['resume' => $renderable]);
    }

    public function export(Resume $resume, ResumeRenderingService $renderingService, PdfGeneratorInterface $pdfGenerator)
    {
        $this->authorize('view', $resume);

        try {
            $renderable = $renderingService->render($resume);

            $view = 'templates.' . ($resume->template?->key ?? 'modern');
            if (! view()->exists($view)) {
                $view = 'templates.modern';
            }

            $html = view($view, ['resume' => $renderable])->render();

            $pdf = $pdfGenerator->generate($html);

            $filename = \Illuminate\Support\Str::slug($resume->name) . '.pdf';

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf),
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF export failed for resume ' . $resume->id . ': ' . $e->getMessage(), ['exception' => $e]);

            if (request()->expectsJson()) {
                return response()->json(['message' => 'PDF generation failed. Please try again later.'], 500);
            }

            return back()->withErrors(['pdf' => 'PDF generation failed. Please try again later.'])->withInput();
        }
    }
}
