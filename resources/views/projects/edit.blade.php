<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Edit Project</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— {{ $project->name }}</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
                @csrf @method('PUT')
                <div>
                    <x-input-label for="name" :value="__('Name *')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $project->name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="role" :value="__('Role')" />
                    <x-text-input id="role" name="role" type="text" class="mt-1.5 block w-full" :value="old('role', $project->role)" />
                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">{{ old('description', $project->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1.5 block w-full" :value="old('start_date', $project->start_date?->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1.5 block w-full" :value="old('end_date', $project->end_date?->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                    </div>
                </div>
                <div>
                    <x-input-label for="project_url" :value="__('Project URL')" />
                    <x-text-input id="project_url" name="project_url" type="url" class="mt-1.5 block w-full" :value="old('project_url', $project->project_url)" />
                    <x-input-error class="mt-2" :messages="$errors->get('project_url')" />
                </div>
                <div>
                    <x-input-label for="github_url" :value="__('GitHub URL')" />
                    <x-text-input id="github_url" name="github_url" type="url" class="mt-1.5 block w-full" :value="old('github_url', $project->github_url)" />
                    <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
                </div>
                <div>
                    <x-input-label :value="__('Technologies')" />
                    <div class="mt-3 space-y-3">
                        @foreach($project->technologies as $idx=>$tech)
                            <div class="flex gap-3 p-3 rounded-xl border border-slate-200 bg-white">
                                <input type="hidden" name="technologies[{{ $idx }}][id]" value="{{ $tech->id }}">
                                <input type="text" name="technologies[{{ $idx }}][name]" value="{{ old("technologies.$idx.name", $tech->name) }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                                <input type="number" name="technologies[{{ $idx }}][sort_order]" value="{{ old("technologies.$idx.sort_order", $tech->sort_order) }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm">
                                <label class="flex items-center gap-1.5 text-xs font-medium text-rose-700 cursor-pointer">
                                    <input type="checkbox" name="technologies[{{ $idx }}][_delete]" value="1" class="w-4 h-4 rounded border-slate-300 text-rose-700 focus:ring-rose-200"> {{ __('Del') }}
                                </label>
                            </div>
                        @endforeach
                        <div class="pt-2 border-t border-dashed border-slate-200">
                            <p class="text-xs font-medium text-slate-600 mb-2">{{ __('Add new:') }}</p>
                            @for($i=$project->technologies->count(); $i<$project->technologies->count()+2; $i++)
                                <div class="flex gap-3 p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 mb-3">
                                    <input type="text" name="technologies[{{ $i }}][name]" placeholder="{{ __('Technology') }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100">
                                    <input type="number" name="technologies[{{ $i }}][sort_order]" value="{{ $i }}" class="w-20 rounded-lg border-slate-200 bg-white text-sm">
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('projects.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Update') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
