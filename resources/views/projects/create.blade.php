<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Add Project</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— new build</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Name *')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name')" required placeholder="e.g. CareerForge" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="role" :value="__('Role')" />
                    <x-text-input id="role" name="role" type="text" class="mt-1.5 block w-full" :value="old('role')" placeholder="e.g. Lead Developer" />
                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" placeholder="What did you build and why?" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">{{ old('description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1.5 block w-full" :value="old('start_date')" />
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1.5 block w-full" :value="old('end_date')" />
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                    </div>
                </div>
                <div>
                    <x-input-label for="project_url" :value="__('Project URL')" />
                    <x-text-input id="project_url" name="project_url" type="url" class="mt-1.5 block w-full" :value="old('project_url')" placeholder="https://..." />
                    <x-input-error class="mt-2" :messages="$errors->get('project_url')" />
                </div>
                <div>
                    <x-input-label for="github_url" :value="__('GitHub URL')" />
                    <x-text-input id="github_url" name="github_url" type="url" class="mt-1.5 block w-full" :value="old('github_url')" placeholder="https://github.com/..." />
                    <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
                </div>
                <div>
                    <x-input-label :value="__('Technologies')" />
                    <p class="mt-1 text-xs text-slate-500">e.g. Laravel, Vue, MySQL — leave blank to ignore.</p>
                    <div class="mt-3 space-y-3">
                        @for($i=0;$i<3;$i++)
                            <div class="flex gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                                <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 grid place-items-center text-xs text-slate-500">{{ $i+1 }}</span>
                                <input type="text" name="technologies[{{ $i }}][name]" value="{{ old("technologies.$i.name") }}" placeholder="{{ __('Technology') }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                                <input type="number" name="technologies[{{ $i }}][sort_order]" value="{{ old("technologies.$i.sort_order", $i) }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm" min="0">
                            </div>
                        @endfor
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('projects.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
