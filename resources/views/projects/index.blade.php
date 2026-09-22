<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Projects</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— showcase & technologies</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header
            title="Projects"
            description="Showcase your projects with technologies — especially valuable as a student with limited formal work experience."
        >
            <x-slot name="action">
                <x-button href="{{ route('projects.create') }}" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Project') }}
                </x-button>
            </x-slot>
        </x-page-header>

        @if (session('status'))<x-alert type="success">{{ session('status') }}</x-alert>@endif

        @forelse ($projects as $project)
            <x-card padding="p-0" class="overflow-hidden">
                <div class="p-6 sm:p-7">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-heading text-base font-semibold tracking-tight text-ink-900 leading-tight">{{ $project->name }} @if($project->role)<span class="font-normal text-slate-500 text-sm">— {{ $project->role }}</span>@endif</h3>
                            @if($project->description)<p class="mt-2 text-sm leading-relaxed text-slate-700">{{ $project->description }}</p>@endif
                            @if($project->technologies->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach($project->technologies as $tech)<x-badge variant="neutral">{{ $tech->name }}</x-badge>@endforeach
                                </div>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                @if($project->start_date || $project->end_date)
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @if($project->start_date){{ $project->start_date->format('M Y') }}@endif
                                        @if($project->start_date && $project->end_date) – {{ $project->end_date->format('M Y') }}@elseif($project->end_date){{ $project->end_date->format('M Y') }}@endif
                                    </span>
                                @endif
                                @if($project->project_url)<a href="{{ $project->project_url }}" target="_blank" class="text-ink-700 hover:text-ink-900 hover:underline inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>{{ $project->project_url }}</a>@endif
                                @if($project->github_url)<a href="{{ $project->github_url }}" target="_blank" class="text-slate-600 hover:text-ink-900 hover:underline inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.852 0 1.336-.012 2.415-.012 2.742 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>GitHub</a>@endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-button href="{{ route('projects.edit', $project) }}" variant="secondary" size="sm">{{ __('Edit') }}</x-button>
                            <x-confirm-delete :action="route('projects.destroy', $project)" />
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-empty-state
                title="{{ __('No projects yet.') }}"
                description="{{ __('Add projects to showcase relevant work — especially valuable with limited job experience.') }}"
            >
                <x-button href="{{ route('projects.create') }}" variant="primary">{{ __('Add Project') }}</x-button>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
