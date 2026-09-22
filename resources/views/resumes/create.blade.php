<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Create Resume</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— tailored for a role</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-page-header
            title="New resume"
            description="Name it for the role you’re targeting. You’ll pick sections and items next — nothing is copied, only referenced."
        />

        <x-card>
            <form method="POST" action="{{ route('resumes.store') }}" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Resume Name *')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name')" placeholder="e.g. Junior IT Support Resume" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="target_role" :value="__('Target Role')" />
                    <x-text-input id="target_role" name="target_role" type="text" class="mt-1.5 block w-full" :value="old('target_role')" placeholder="e.g. IT Support" />
                    <x-input-error class="mt-2" :messages="$errors->get('target_role')" />
                    <p class="mt-1.5 text-xs text-slate-500">Shown in the header of your resume and helps you keep variants organized.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="template_id" :value="__('Template *')" />
                        <select id="template_id" name="template_id" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm" required>
                            <option value="">{{ __('Select template') }}</option>
                            @foreach($templates as $tpl)<option value="{{ $tpl->id }}" {{ old('template_id')==$tpl->id?'selected':'' }}>{{ $tpl->name }} ({{ $tpl->key }})</option>@endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('template_id')" />
                    </div>
                    <div>
                        <x-input-label for="theme_id" :value="__('Theme *')" />
                        <select id="theme_id" name="theme_id" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm" required>
                            <option value="">{{ __('Select theme') }}</option>
                            @foreach($themes as $theme)<option value="{{ $theme->id }}" {{ old('theme_id')==$theme->id?'selected':'' }}>{{ $theme->name }}</option>@endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('theme_id')" />
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <x-button href="{{ route('resumes.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" variant="primary">{{ __('Create') }}</x-button>
                </div>
            </form>
        </x-card>

        <div class="mt-6 p-4 rounded-xl border border-honey-100 bg-honey-50">
            <p class="text-sm font-medium text-ink-900">What happens next?</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">You’ll be taken to the builder where you can toggle sections (Experience, Education, Skills, Projects) and pick exactly which items appear — with reordering.</p>
        </div>
    </div>
</x-app-layout>
