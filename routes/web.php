<?php

use App\Http\Controllers\EducationController;
use App\Http\Controllers\ExperienceAchievementController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ResumeItemController;
use App\Http\Controllers\ResumeSectionController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/career-profile', [ProfileController::class, 'edit'])->name('career-profile.edit');
    Route::put('/career-profile', [ProfileController::class, 'update'])->name('career-profile.update');
    Route::get('/career-profile/photo', [ProfileController::class, 'photo'])->name('career-profile.photo');
    Route::delete('/career-profile/photo', [ProfileController::class, 'destroyPhoto'])->name('career-profile.photo.destroy');

    Route::resource('experiences', ExperienceController::class)->except(['show']);
    Route::resource('experiences.achievements', ExperienceAchievementController::class)->shallow()->except(['show']);

    Route::resource('educations', EducationController::class)->except(['show']);
    Route::resource('skills', SkillController::class)->except(['show']);
    Route::resource('projects', ProjectController::class)->except(['show']);

    Route::get('/resumes/{resume}/preview', [ResumeController::class, 'preview'])->name('resumes.preview');
    Route::post('/resumes/{resume}/export', [ResumeController::class, 'export'])->name('resumes.export');
    Route::post('/resumes/{resume}/duplicate', [ResumeController::class, 'duplicate'])->name('resumes.duplicate');
    Route::resource('resumes', ResumeController::class);
    Route::put('/resumes/{resume}/sections/reorder', [ResumeSectionController::class, 'reorder'])->name('resumes.sections.reorder');
    Route::post('/resumes/{resume}/sections', [ResumeSectionController::class, 'store'])->name('resumes.sections.store');
    Route::put('/resumes/{resume}/sections/{section}', [ResumeSectionController::class, 'update'])->name('resumes.sections.update');
    Route::delete('/resumes/{resume}/sections/{section}', [ResumeSectionController::class, 'destroy'])->name('resumes.sections.destroy');
    Route::post('/resumes/{resume}/items', [ResumeItemController::class, 'store'])->name('resumes.items.store');
    Route::delete('/resume-items/{resumeItem}', [ResumeItemController::class, 'destroy'])->name('resume-items.destroy');
    Route::put('/resume-items/reorder', [ResumeItemController::class, 'reorder'])->name('resume-items.reorder');
});

require __DIR__.'/auth.php';
