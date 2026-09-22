<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Skills</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— grouped by category</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header
            title="Skills"
            description="Capture what you’re good at — organized so resumes can pull the right group for each role."
        >
            <x-slot name="action">
                <x-button href="{{ route('skills.create') }}" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Skill') }}
                </x-button>
            </x-slot>
        </x-page-header>

        @if (session('status'))<x-alert type="success">{{ session('status') }}</x-alert>@endif

        @forelse ($allSkills as $skill)
            <x-card padding="p-0" class="flex items-center justify-between p-5">
                <div class="flex items-center gap-4 min-w-0">
                    <span class="hidden sm:grid place-items-center w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold tracking-tight text-ink-900">{{ $skill->name }}</span>
                            <x-badge variant="neutral">{{ $skill->category }}</x-badge>
                            @if($skill->proficiency)<x-badge variant="accent">{{ $skill->proficiency }}/5</x-badge>@endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <x-button href="{{ route('skills.edit', $skill) }}" variant="secondary" size="sm">{{ __('Edit') }}</x-button>
                    <x-confirm-delete :action="route('skills.destroy', $skill)" label="{{ __('Delete') }}" />
                </div>
            </x-card>
        @empty
            <x-empty-state
                title="{{ __('No skills yet.') }}"
                description="{{ __('Add skills grouped by category — e.g., Technical, Soft, Tools — so each resume can surface the right ones.') }}"
            >
                <x-button href="{{ route('skills.create') }}" variant="primary">{{ __('Add Skill') }}</x-button>
            </x-empty-state>
        @endforelse

        @if($skills->isNotEmpty())
            <x-card>
                <h3 class="font-heading text-sm font-semibold tracking-tight text-ink-900">By Category</h3>
                <div class="mt-4 space-y-5">
                    @foreach($skills as $category => $group)
                        <div>
                            <h4 class="text-xs font-semibold tracking-widest uppercase text-slate-500">{{ str_replace('_', ' ', $category) }}</h4>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($group as $s)
                                    <span class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-ink-800">{{ $s->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>
</x-app-layout>
