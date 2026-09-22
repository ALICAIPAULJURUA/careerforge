<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">{{ $resume->name }}</h2>
            <x-badge variant="neutral">{{ $resume->template->name ?? 'Modern' }}</x-badge>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        @if($errors->has('pdf'))
            <x-alert type="error">{{ $errors->first('pdf') }}</x-alert>
        @endif
        @if(session('status'))
            <x-alert type="success">{{ session('status') }}</x-alert>
        @endif

        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-600">
                        <span class="font-medium text-ink-900">{{ $resume->template->name }}</span>
                        <span class="text-slate-400 mx-2">•</span>
                        <span>{{ $resume->theme->name }}</span>
                        @if($resume->target_role)<span class="text-slate-400 mx-2">•</span><span>{{ __('Target') }}: {{ $resume->target_role }}</span>@endif
                    </p>
                    <p class="mt-1 text-xs text-slate-500">{{ $resume->sections->count() }} sections • Updated {{ $resume->updated_at->diffForHumans() }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-button href="{{ route('resumes.edit', $resume) }}" variant="secondary" size="sm">Edit</x-button>
                    <x-button href="{{ route('resumes.preview', $resume) }}" target="_blank" variant="ghost" size="sm">Preview</x-button>
                    <form method="POST" action="{{ route('resumes.export', $resume) }}" class="inline">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm">Export PDF</x-button>
                    </form>
                </div>
            </div>
        </x-card>

        @forelse($resume->sections->sortBy('sort_order') as $section)
            <x-card>
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-ink-900 text-white grid place-items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <h3 class="font-heading text-sm font-semibold tracking-tight uppercase text-ink-900">{{ $section->section_type }}</h3>
                    @if(!$section->is_visible)<x-badge variant="neutral">Hidden</x-badge>@endif
                    <span class="ml-auto text-xs text-slate-500">{{ $section->items->count() }} items</span>
                </div>
                <ul class="space-y-2">
                    @forelse($section->items->sortBy('sort_order') as $item)
                        <li class="flex gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50 text-sm text-slate-800">
                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-honey-600 shrink-0"></span>
                            <span class="flex-1 min-w-0 leading-relaxed">{{ class_basename($item->itemable_type) }} #{{ $item->itemable_id }}: {{ $item->itemable->job_title ?? $item->itemable->qualification ?? $item->itemable->name ?? $item->itemable->content ?? 'Item' }}</span>
                        </li>
                    @empty
                        <li class="p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500 italic text-center">{{ __('No items selected.') }}</li>
                    @endforelse
                </ul>
            </x-card>
        @empty
            <x-empty-state
                title="{{ __('No sections configured yet. Edit to add sections.') }}"
                description="{{ __('Your resume is empty — add sections and pick items to build it.') }}"
            >
                <x-button href="{{ route('resumes.edit', $resume) }}" variant="primary">Build resume</x-button>
            </x-empty-state>
        @endforelse
    </div>
</x-app-layout>
