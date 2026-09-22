<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Career Profile</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— your source of truth</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-8">
        <x-page-header
            title="Personal Information"
            description="This information anchors every resume you create. Keep it complete and current — it will appear wherever you choose to include it."
        />

        @if (session('status') === 'profile-updated')
            <x-alert type="success">{{ __('Saved.') }}</x-alert>
        @endif
        @if (session('status') === 'photo-removed')
            <x-alert type="success">{{ __('Photo removed.') }}</x-alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[1.35fr_0.85fr] gap-6">
            <!-- Form -->
            <x-card>
                <form method="POST" action="{{ route('career-profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="full_name" :value="__('Full Name')" />
                            <x-text-input id="full_name" name="full_name" type="text" class="mt-1.5 block w-full" :value="old('full_name', $profile->full_name)" autocomplete="name" placeholder="Alex Morgan" />
                            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="professional_title" :value="__('Professional Title')" />
                            <x-text-input id="professional_title" name="professional_title" type="text" class="mt-1.5 block w-full" :value="old('professional_title', $profile->professional_title)" placeholder="Senior Product Designer" />
                            <x-input-error class="mt-2" :messages="$errors->get('professional_title')" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1.5 block w-full" :value="old('phone', $profile->phone)" autocomplete="tel" placeholder="+49 170 1234567" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input id="location" name="location" type="text" class="mt-1.5 block w-full" :value="old('location', $profile->location)" placeholder="Berlin, Germany" />
                            <x-input-error class="mt-2" :messages="$errors->get('location')" />
                        </div>

                        <div>
                            <x-input-label for="website_url" :value="__('Website URL')" />
                            <x-text-input id="website_url" name="website_url" type="url" class="mt-1.5 block w-full" :value="old('website_url', $profile->website_url)" placeholder="https://example.com" />
                            <x-input-error class="mt-2" :messages="$errors->get('website_url')" />
                        </div>

                        <div>
                            <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" />
                            <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1.5 block w-full" :value="old('linkedin_url', $profile->linkedin_url)" placeholder="https://linkedin.com/in/..." />
                            <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="github_url" :value="__('GitHub URL')" />
                            <x-text-input id="github_url" name="github_url" type="url" class="mt-1.5 block w-full" :value="old('github_url', $profile->github_url)" placeholder="https://github.com/..." />
                            <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <x-input-label for="photo" :value="__('Profile Photo')" />
                        <p class="mt-1 text-xs text-slate-500">This photo appears only where you enable it — per-resume. JPEG, PNG, WebP. Max 2MB.</p>

                        @if ($profile->photo_path)
                            <div class="mt-4 flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                                <img src="{{ route('career-profile.photo') }}" alt="Profile photo" class="h-20 w-20 rounded-xl object-cover border border-white shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-ink-900">Current photo</p>
                                    <label for="remove_photo" class="mt-1 inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                                        <input type="checkbox" id="remove_photo" name="remove_photo" value="1" class="w-4 h-4 rounded border-slate-300 text-ink-800 focus:ring-ink-200">
                                        {{ __('Remove current photo on save') }}
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-ink-900 file:text-white hover:file:bg-ink-800 file:transition-colors" />
                            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                            <x-input-error class="mt-2" :messages="$errors->get('remove_photo')" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
                        <span class="text-xs text-slate-500">Updates apply to all future resumes.</span>
                    </div>
                </form>
            </x-card>

            <!-- Side card -->
            <div class="space-y-6">
                <x-card padding="p-6" class="bg-ink-900 border-ink-900 text-white">
                    <h3 class="font-heading text-base font-semibold text-white">How this works</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        Your profile is the <span class="text-honey-100 font-medium">single source of truth</span>. Resumes never copy this data — they reference it. Edit here, and every resume that includes the item stays in sync.
                    </p>
                    <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-xl bg-white/5 border border-white/10 py-3">
                            <div class="text-sm font-semibold text-white">{{ auth()->user()->experiences()->count() }}</div>
                            <div class="text-[11px] tracking-widest uppercase text-slate-400">Experiences</div>
                        </div>
                        <div class="rounded-xl bg-white/5 border border-white/10 py-3">
                            <div class="text-sm font-semibold text-white">{{ auth()->user()->educations()->count() }}</div>
                            <div class="text-[11px] tracking-widest uppercase text-slate-400">Education</div>
                        </div>
                        <div class="rounded-xl bg-white/5 border border-white/10 py-3">
                            <div class="text-sm font-semibold text-white">{{ auth()->user()->skills()->count() }}</div>
                            <div class="text-[11px] tracking-widest uppercase text-slate-400">Skills</div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <x-button href="{{ route('experiences.index') }}" variant="secondary" size="sm" class="!bg-white !text-ink-900 !border-white hover:!bg-slate-100">Manage experience</x-button>
                        <x-button href="{{ route('resumes.index') }}" variant="ghost" size="sm" class="!text-white hover:!bg-white/10">Go to resumes</x-button>
                    </div>
                </x-card>

                @if ($profile->photo_path)
                    <x-card>
                        <h4 class="font-medium text-sm text-ink-900">Photo handling</h4>
                        <p class="mt-1 text-sm text-slate-600">Photos are stored on a private disk and served only after policy checks. They never become public URLs.</p>
                        <form method="POST" action="{{ route('career-profile.photo.destroy') }}" class="mt-4">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm" class="w-full justify-center">
                                {{ __('Delete Photo') }}
                            </x-button>
                        </form>
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
