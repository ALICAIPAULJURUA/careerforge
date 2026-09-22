<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Add Experience</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— a role in your history</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-page-header
            title="New role"
            description="Add a position you’ve held. You can attach achievements here now, or add them later from the list."
        />

        <x-card>
            <form method="POST" action="{{ route('experiences.store') }}" class="space-y-7">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <x-input-label for="job_title" :value="__('Job Title *')" />
                        <x-text-input id="job_title" name="job_title" type="text" class="mt-1.5 block w-full" :value="old('job_title')" required placeholder="e.g. Frontend Developer" />
                        <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="organization" :value="__('Organization *')" />
                        <x-text-input id="organization" name="organization" type="text" class="mt-1.5 block w-full" :value="old('organization')" required placeholder="e.g. Acme Studios" />
                        <x-input-error class="mt-2" :messages="$errors->get('organization')" />
                    </div>

                    <div>
                        <x-input-label for="location" :value="__('Location')" />
                        <x-text-input id="location" name="location" type="text" class="mt-1.5 block w-full" :value="old('location')" placeholder="Berlin, Germany" />
                        <x-input-error class="mt-2" :messages="$errors->get('location')" />
                    </div>

                    <div class="sm:contents">
                        <div>
                            <x-input-label for="start_date" :value="__('Start Date *')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1.5 block w-full" :value="old('start_date')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('End Date')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1.5 block w-full" :value="old('end_date')" />
                            <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                            <label class="mt-3 flex items-center gap-2.5 cursor-pointer group">
                                <input type="checkbox" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-ink-800 focus:ring-ink-200 focus:ring-4">
                                <span class="text-sm text-slate-700 group-hover:text-ink-900">{{ __('Current position (no end date)') }}</span>
                            </label>
                            <x-input-error class="mt-2" :messages="$errors->get('is_current')" />
                        </div>
                    </div>
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" placeholder="A brief summary of the role and context..." class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">{{ old('description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <h4 class="font-heading text-sm font-semibold tracking-tight text-ink-900">{{ __('Achievements') }} <span class="ml-2 text-xs font-normal tracking-wide text-slate-500">({{ __('optional') }})</span></h4>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ __('Add bullet points for this role. You can reorder later. Leave empty rows blank — they’ll be ignored.') }}</p>
                    <div class="mt-4 space-y-3">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="flex gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                                <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 grid place-items-center text-xs font-medium text-slate-500 shrink-0">{{ $i + 1 }}</span>
                                <div class="flex-1 flex gap-2">
                                    <input type="text" name="achievements[{{ $i }}][content]" value="{{ old("achievements.$i.content") }}" placeholder="{{ __('Achievement #') }}{{ $i+1 }} — e.g. Shipped checkout redesign" class="flex-1 rounded-lg border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                                    <input type="number" name="achievements[{{ $i }}][sort_order]" value="{{ old("achievements.$i.sort_order", $i) }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100" min="0" placeholder="{{ __('Order') }}">
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('achievements.'.$i.'.content')" />
                        @endfor
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('experiences.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Save Experience') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
