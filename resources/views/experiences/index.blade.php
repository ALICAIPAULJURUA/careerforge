<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Work Experiences</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— roles & achievements</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header
            title="Experience"
            description="Manage your work history. Each role holds its own achievements — you’ll pick which ones appear per resume."
        >
            <x-slot name="action">
                <x-button href="{{ route('experiences.create') }}" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Experience') }}
                </x-button>
            </x-slot>
        </x-page-header>

        @if (session('status'))
            <x-alert type="success">{{ session('status') }}</x-alert>
        @endif

        @forelse ($experiences as $experience)
            <x-card padding="p-0" class="overflow-hidden">
                <div class="p-6 sm:p-7">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-heading text-lg font-semibold tracking-tight text-ink-900 leading-tight">
                                {{ $experience->job_title }} <span class="font-normal text-slate-400 mx-1">—</span> <span class="font-medium text-slate-700">{{ $experience->organization }}</span>
                            </h3>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                                @if($experience->location)
                                    <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>{{ $experience->location }}</span>
                                    <span class="text-slate-300">•</span>
                                @endif
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $experience->start_date->format('M Y') }} – @if ($experience->is_current) {{ __('Present') }} @elseif ($experience->end_date) {{ $experience->end_date->format('M Y') }} @else {{ __('—') }} @endif
                                </span>
                                @if ($experience->is_current)
                                    <x-badge variant="success">{{ __('Current') }}</x-badge>
                                @endif
                            </div>
                            @if ($experience->description)
                                <p class="mt-3 text-sm leading-relaxed text-slate-700 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5">{{ $experience->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-button href="{{ route('experiences.edit', $experience) }}" variant="secondary" size="sm">{{ __('Edit') }}</x-button>
                            <x-confirm-delete :action="route('experiences.destroy', $experience)" label="{{ __('Delete') }}" confirmTitle="{{ __('Delete this experience?') }}" confirmText="{{ __('Achievements will also be removed.') }}" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center gap-2 mb-3">
                            <h4 class="text-xs font-semibold tracking-widest uppercase text-slate-500">Achievements</h4>
                            <span class="text-xs text-slate-400">— {{ $experience->achievements->count() }} total</span>
                        </div>

                        @if ($experience->achievements->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach ($experience->achievements->sortBy('sort_order') as $achievement)
                                    <li class="group flex items-start justify-between gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:border-ink-200 hover:bg-slate-50 transition-colors">
                                        <span class="flex gap-3 text-sm text-slate-800 flex-1 min-w-0">
                                            <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-honey-600 shrink-0"></span>
                                            <span class="leading-relaxed">{{ $achievement->content }}</span>
                                        </span>
                                        <x-confirm-delete :action="route('achievements.destroy', $achievement)" label="{{ __('Remove') }}" confirmTitle="{{ __('Remove achievement?') }}" class="!px-2.5 !py-1.5 !text-xs" />
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-500 italic bg-slate-50 border border-dashed border-slate-200 rounded-xl px-4 py-3">{{ __('No achievements yet.') }}</p>
                        @endif

                        <form method="POST" action="{{ route('experiences.achievements.store', $experience) }}" class="mt-4 flex flex-col sm:flex-row gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                            @csrf
                            <input type="text" name="content" placeholder="{{ __('Add achievement...') }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-ink-300 focus:ring-4 focus:ring-ink-100" required>
                            <input type="number" name="sort_order" placeholder="{{ __('Order') }}" class="w-full sm:w-24 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100" min="0">
                            <x-button type="submit" variant="primary" size="sm" class="shrink-0">{{ __('Add') }}</x-button>
                        </form>
                    </div>
                </div>
            </x-card>
        @empty
            <x-empty-state
                title="{{ __('No experiences yet. Add your first role.') }}"
                description="{{ __('Your work history is the backbone of every resume. Add a role and its key achievements to get started.') }}"
            >
                <x-button href="{{ route('experiences.create') }}" variant="primary">{{ __('Add Experience') }}</x-button>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
