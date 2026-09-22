<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Education</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— degrees & studies</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-page-header
            title="Education"
            description="Your academic background. This section appears prominently on entry-level resumes."
        >
            <x-slot name="action">
                <x-button href="{{ route('educations.create') }}" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Education') }}
                </x-button>
            </x-slot>
        </x-page-header>

        @if (session('status'))<x-alert type="success" class="border-honey-200 bg-honey-50 text-ink-900">{{ session('status') }}</x-alert>@endif

        @forelse ($educations as $education)
            <x-card padding="p-0" class="overflow-hidden">
                <div class="p-6 sm:p-7">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-heading text-base font-semibold tracking-tight text-ink-900 leading-tight">
                                {{ $education->qualification }} <span class="font-normal text-slate-400 mx-1">—</span> <span class="font-medium text-slate-700">{{ $education->institution }}</span>
                            </h3>
                            @if($education->field_of_study)
                                <p class="mt-1 text-sm text-slate-600">{{ $education->field_of_study }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $education->start_date->format('M Y') }} – @if($education->is_current) {{ __('Present') }} @elseif($education->end_date) {{ $education->end_date->format('M Y') }} @else — @endif
                                </span>
                                @if($education->is_current)<x-badge variant="success">{{ __('Current') }}</x-badge>@endif
                            </div>
                            @if($education->description)
                                <p class="mt-3 text-sm leading-relaxed text-slate-700 bg-slate-50 border border-slate-100 rounded-xl px-3.5 py-2.5">{{ $education->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-button href="{{ route('educations.edit', $education) }}" variant="secondary" size="sm">{{ __('Edit') }}</x-button>
                            <x-confirm-delete :action="route('educations.destroy', $education)" label="{{ __('Delete') }}" />
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-empty-state
                title="{{ __('No education entries yet.') }}"
                description="{{ __('Add your degrees and studies — they anchor the Education section of every resume.') }}"
            >
                <x-button href="{{ route('educations.create') }}" variant="primary">{{ __('Add Education') }}</x-button>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
