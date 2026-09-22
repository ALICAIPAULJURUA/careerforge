<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Add Education</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— new qualification</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <form method="POST" action="{{ route('educations.store') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <x-input-label for="institution" :value="__('Institution *')" />
                        <x-text-input id="institution" name="institution" type="text" class="mt-1.5 block w-full" :value="old('institution')" required placeholder="e.g. University of Berlin" />
                        <x-input-error class="mt-2" :messages="$errors->get('institution')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="qualification" :value="__('Qualification *')" />
                        <x-text-input id="qualification" name="qualification" type="text" class="mt-1.5 block w-full" :value="old('qualification')" required placeholder="e.g. B.Sc. Computer Science" />
                        <x-input-error class="mt-2" :messages="$errors->get('qualification')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="field_of_study" :value="__('Field of Study')" />
                        <x-text-input id="field_of_study" name="field_of_study" type="text" class="mt-1.5 block w-full" :value="old('field_of_study')" placeholder="e.g. Artificial Intelligence" />
                        <x-input-error class="mt-2" :messages="$errors->get('field_of_study')" />
                    </div>
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date *')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1.5 block w-full" :value="old('start_date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1.5 block w-full" :value="old('end_date')" />
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                        <label class="mt-3 flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-ink-800 focus:ring-ink-200 focus:ring-4">
                            <span class="text-sm text-slate-700">{{ __('Currently studying') }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" placeholder="Thesis, honors, relevant coursework..." class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">{{ old('description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('educations.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
