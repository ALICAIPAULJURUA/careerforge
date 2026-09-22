<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Edit Experience</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— {{ $experience->job_title }}</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <form method="POST" action="{{ route('experiences.update', $experience) }}" class="space-y-7">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <x-input-label for="job_title" :value="__('Job Title *')" />
                        <x-text-input id="job_title" name="job_title" type="text" class="mt-1.5 block w-full" :value="old('job_title', $experience->job_title)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="organization" :value="__('Organization *')" />
                        <x-text-input id="organization" name="organization" type="text" class="mt-1.5 block w-full" :value="old('organization', $experience->organization)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('organization')" />
                    </div>
                    <div>
                        <x-input-label for="location" :value="__('Location')" />
                        <x-text-input id="location" name="location" type="text" class="mt-1.5 block w-full" :value="old('location', $experience->location)" />
                        <x-input-error class="mt-2" :messages="$errors->get('location')" />
                    </div>
                    <div class="sm:contents">
                        <div>
                            <x-input-label for="start_date" :value="__('Start Date *')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1.5 block w-full" :value="old('start_date', $experience->start_date?->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('End Date')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1.5 block w-full" :value="old('end_date', $experience->end_date?->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                            <label class="mt-3 flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_current" value="1" {{ old('is_current', $experience->is_current) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-ink-800 focus:ring-ink-200 focus:ring-4">
                                <span class="text-sm text-slate-700">{{ __('Current position (no end date)') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">{{ old('description', $experience->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <h4 class="font-heading text-sm font-semibold tracking-tight text-ink-900">{{ __('Achievements') }}</h4>
                    <p class="mt-1 text-xs text-slate-500">Edit existing or add new. Check “Del” to remove on save.</p>
                    <div class="mt-4 space-y-3">
                        @foreach ($experience->achievements->sortBy('sort_order') as $idx => $ach)
                            <div class="flex gap-3 p-3 rounded-xl border border-slate-200 bg-white">
                                <input type="hidden" name="achievements[{{ $idx }}][id]" value="{{ $ach->id }}">
                                <input type="text" name="achievements[{{ $idx }}][content]" value="{{ old("achievements.$idx.content", $ach->content) }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                                <input type="number" name="achievements[{{ $idx }}][sort_order]" value="{{ old("achievements.$idx.sort_order", $ach->sort_order) }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100" min="0">
                                <label class="flex items-center gap-1.5 text-xs font-medium text-rose-700 cursor-pointer">
                                    <input type="checkbox" name="achievements[{{ $idx }}][_delete]" value="1" class="w-4 h-4 rounded border-slate-300 text-rose-700 focus:ring-rose-200">
                                    {{ __('Del') }}
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('achievements.'.$idx.'.content')" class="mb-2" />
                        @endforeach
                        <div class="pt-2">
                            <p class="text-xs font-medium text-slate-700 mb-2">{{ __('Add new:') }}</p>
                            @for ($i = $experience->achievements->count(); $i < $experience->achievements->count()+2; $i++)
                                <div class="flex gap-3 p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 mb-3">
                                    <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 grid place-items-center text-xs text-slate-500">{{ $i - $experience->achievements->count() + 1 }}</span>
                                    <input type="text" name="achievements[{{ $i }}][content]" value="{{ old("achievements.$i.content") }}" placeholder="{{ __('New achievement') }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                                    <input type="number" name="achievements[{{ $i }}][sort_order]" value="{{ old("achievements.$i.sort_order", $i) }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm" min="0">
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('experiences.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Update Experience') }}</x-button>
                </div>
            </form>
        </x-card>

        <x-card class="mt-6">
            <h4 class="font-heading text-sm font-semibold text-ink-900">Manage achievements individually</h4>
            <p class="mt-1 text-xs text-slate-500">Quick edits without saving the whole experience.</p>
            <div class="mt-4 space-y-3">
                @foreach ($experience->achievements as $ach)
                    <form method="POST" action="{{ route('achievements.update', $ach) }}" class="flex gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50">
                        @csrf
                        @method('PUT')
                        <input type="text" name="content" value="{{ $ach->content }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                        <input type="number" name="sort_order" value="{{ $ach->sort_order }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm">
                        <x-button type="submit" variant="secondary" size="sm">{{ __('Save') }}</x-button>
                    </form>
                    <form method="POST" action="{{ route('achievements.destroy', $ach) }}" class="mb-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-rose-700 hover:text-rose-800 hover:underline">{{ __('Delete achievement') }}</button>
                    </form>
                @endforeach
                <form method="POST" action="{{ route('experiences.achievements.store', $experience) }}" class="flex gap-2 p-3 rounded-xl border border-slate-200 bg-white">
                    @csrf
                    <input type="text" name="content" placeholder="{{ __('New achievement') }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100" required>
                    <input type="number" name="sort_order" placeholder="{{ __('Order') }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm">
                    <x-button type="submit" variant="primary" size="sm">{{ __('Add') }}</x-button>
                </form>
            </div>
        </x-card>
    </div>
</x-app-layout>
