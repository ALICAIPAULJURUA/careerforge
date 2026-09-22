<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">My Resumes</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— {{ $resumes->count() }} {{ Str::plural('document', $resumes->count()) }}</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header
            title="Resumes"
            description="Each resume is a curated lens on your profile. Duplicate to tailor for a new role — your source data stays untouched."
        >
            <x-slot name="action">
                <x-button href="{{ route('resumes.create') }}" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Create Resume') }}
                </x-button>
            </x-slot>
        </x-page-header>

        @if(session('status'))
            <x-alert type="success">{{ session('status') }}</x-alert>
        @endif
        @if($errors->has('pdf'))
            <x-alert type="error">{{ $errors->first('pdf') }}</x-alert>
        @endif

        @forelse($resumes as $resume)
            <x-card padding="p-0" class="overflow-hidden hover:shadow-soft transition-shadow">
                <div class="flex flex-col lg:flex-row">
                    <!-- Left accent -->
                    <div class="hidden lg:block w-1.5 bg-ink-900 shrink-0"></div>
                    <div class="flex-1 p-6 sm:p-7">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-heading text-lg font-semibold tracking-tight text-ink-900 leading-tight">{{ $resume->name }}</h3>
                                    @if($resume->target_role)
                                        <x-badge variant="neutral">{{ $resume->target_role }}</x-badge>
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-honey-600"></span>
                                        {{ $resume->template->name ?? $resume->template_id }}
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span>{{ $resume->theme->name ?? $resume->theme_id }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span>{{ $resume->sections->count() }} {{ Str::plural('section', $resume->sections->count()) }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span>Updated {{ $resume->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <!-- Primary actions -->
                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <x-button href="{{ route('resumes.edit', $resume) }}" variant="secondary" size="sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ __('Build') }}
                                </x-button>
                                <x-button href="{{ route('resumes.preview', $resume) }}" target="_blank" variant="ghost" size="sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ __('Preview') }}
                                </x-button>
                                <form method="POST" action="{{ route('resumes.export', $resume) }}" class="inline">
                                    @csrf
                                    <x-button type="submit" variant="primary" size="sm" class="!bg-ink-900 hover:!bg-ink-800 !border-ink-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2h-2M5 5a2 2 0 002-2h2"/></svg>
                                        {{ __('PDF') }}
                                    </x-button>
                                </form>
                            </div>
                        </div>

                        <!-- Secondary actions bar -->
                        <div class="mt-5 flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100">
                            <form method="POST" action="{{ route('resumes.duplicate', $resume) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-ink-900 hover:bg-slate-50 rounded-lg border border-transparent hover:border-slate-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    {{ __('Duplicate') }}
                                </button>
                            </form>
                            <a href="{{ route('resumes.show', $resume) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-ink-900 hover:bg-slate-50 rounded-lg border border-transparent hover:border-slate-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('View') }}
                            </a>
                            <span class="text-slate-200">|</span>
                            <x-confirm-delete :action="route('resumes.destroy', $resume)" label="{{ __('Delete') }}" confirmTitle="{{ __('Delete resume?') }}" confirmText="{{ __('Sections and items will be removed. Your profile stays intact.') }}" />
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-empty-state
                title="{{ __('No resumes yet. Create one.') }}"
                description="{{ __('Start with a blank resume, then curate sections and items for each role you target.') }}"
            >
                <x-button href="{{ route('resumes.create') }}" variant="primary">
                    {{ __('Create Resume') }}
                </x-button>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
