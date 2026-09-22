<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Add Skill</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— new capability</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card>
            <form method="POST" action="{{ route('skills.store') }}" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Name *')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name')" required placeholder="e.g. Laravel, Figma, Leadership" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="category" :value="__('Category *')" />
                    <select id="category" name="category" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">
                        @foreach($categories as $cat)<option value="{{ $cat }}" {{ old('category')==$cat?'selected':'' }}>{{ str_replace('_',' ', ucfirst($cat)) }}</option>@endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('category')" />
                </div>
                <div>
                    <x-input-label for="proficiency" :value="__('Proficiency (1-5, optional)')" />
                    <x-text-input id="proficiency" name="proficiency" type="number" min="1" max="5" class="mt-1.5 block w-full" :value="old('proficiency')" placeholder="4" />
                    <x-input-error class="mt-2" :messages="$errors->get('proficiency')" />
                </div>
                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('skills.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
