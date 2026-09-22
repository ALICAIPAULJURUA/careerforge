<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium tracking-widest uppercase text-honey-700">
                    <span class="w-2 h-2 rounded-full bg-honey-600"></span>
                    Resume Builder
                </div>
                <h2 class="mt-1 font-heading text-2xl font-semibold tracking-tight text-ink-900 leading-tight">{{ $resume->name }}</h2>
                @if($resume->target_role)<p class="mt-1 text-sm text-slate-600">Target: <span class="font-medium text-ink-900">{{ $resume->target_role }}</span> • {{ $resume->template->name }} • {{ $resume->theme->name }}</p>@endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-button href="{{ route('resumes.preview', $resume) }}" target="_blank" variant="secondary" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview
                </x-button>
                <form method="POST" action="{{ route('resumes.export', $resume) }}" class="inline">
                    @csrf
                    <x-button type="submit" variant="primary" size="sm">Export PDF</x-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-8">
        @if(session('status'))<x-alert type="success">{{ session('status') }}</x-alert>@endif
        @if($errors->has('pdf'))<x-alert type="error">{{ $errors->first('pdf') }}</x-alert>@endif

        <!-- Resume details -->
        <x-card>
            <div class="flex items-center gap-3 mb-6">
                <span class="w-9 h-9 rounded-xl bg-ink-900 text-white grid place-items-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                <div>
                    <h3 class="font-heading text-base font-semibold text-ink-900">Resume details</h3>
                    <p class="text-xs text-slate-500">Name, target role, template and theme — the frame for this document.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('resumes.update', $resume) }}" class="space-y-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="name" :value="__('Name *')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $resume->name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="target_role" :value="__('Target Role')" />
                        <x-text-input id="target_role" name="target_role" type="text" class="mt-1.5 block w-full" :value="old('target_role', $resume->target_role)" placeholder="e.g. IT Support" />
                        <x-input-error class="mt-2" :messages="$errors->get('target_role')" />
                    </div>
                    <div>
                        <x-input-label for="template_id" :value="__('Template')" />
                        <select name="template_id" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">
                            @foreach($templates as $tpl)<option value="{{ $tpl->id }}" {{ $resume->template_id==$tpl->id?'selected':'' }}>{{ $tpl->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="theme_id" :value="__('Theme')" />
                        <select name="theme_id" class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-slate-900 focus:border-ink-300 focus:ring-4 focus:ring-ink-100 shadow-sm">
                            @foreach($themes as $th)<option value="{{ $th->id }}" {{ $resume->theme_id==$th->id?'selected':'' }}>{{ $th->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <x-button type="submit" variant="primary" size="sm">Save details</x-button>
            </form>
        </x-card>

        <!-- Section manager -->
        <x-card>
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-heading text-base font-semibold text-ink-900">Sections</h3>
                    <p class="mt-1 text-sm text-slate-600">Toggle visibility and order. Only visible sections appear in preview and PDF.</p>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1.5 text-xs text-slate-500"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Visible</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @php
                    $allTypes = ['experience','education','skills','projects','summary','certifications','awards','leadership','languages','references'];
                    $existing = $resume->sections->keyBy('section_type');
                @endphp
                @foreach($allTypes as $type)
                    @php $section = $existing->get($type); @endphp
                    <div class="flex items-center justify-between p-4 rounded-xl border {{ $section ? ($section->is_visible ? 'bg-white border-slate-200 shadow-sm' : 'bg-slate-50 border-slate-200') : 'bg-slate-50/50 border-dashed border-slate-200' }}">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold tracking-tight text-ink-900 uppercase">{{ $type }}</div>
                            @if($section)
                                <div class="text-xs text-slate-500 mt-0.5">Order {{ $section->sort_order }} • {{ $section->items->count() }} items</div>
                            @else
                                <div class="text-xs text-slate-500 mt-0.5">Not added</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($section)
                                <form method="POST" action="{{ route('resumes.sections.update', [$resume, $section]) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="is_visible" value="{{ $section->is_visible ? 0 : 1 }}">
                                    <button type="submit" class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ $section->is_visible ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}">
                                        {{ $section->is_visible ? __('Visible') : __('Hidden') }}
                                    </button>
                                </form>
                                <x-confirm-delete :action="route('resumes.sections.destroy', [$resume, $section])" label="" confirmTitle="{{ __('Remove section?') }}" class="!p-2" />
                            @else
                                <form method="POST" action="{{ route('resumes.sections.store', $resume) }}">
                                    @csrf
                                    <input type="hidden" name="section_type" value="{{ $type }}">
                                    <input type="hidden" name="is_visible" value="1">
                                    <x-button type="submit" variant="secondary" size="sm" class="!px-3 !py-1.5 !text-xs">Add</x-button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <details class="mt-6 group">
                <summary class="cursor-pointer text-xs font-medium text-slate-600 hover:text-ink-900 flex items-center gap-2">
                    <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    Advanced: reorder sections by IDs
                </summary>
                <form method="POST" action="{{ route('resumes.sections.reorder', $resume) }}" class="mt-3 flex gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="ordered_ids" placeholder="e.g. 3,1,2" class="flex-1 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100" />
                    <x-button type="submit" variant="secondary" size="sm">Reorder</x-button>
                </form>
                <p class="mt-1 text-xs text-slate-500">Drag-and-drop is on the roadmap; for now, use comma-separated IDs.</p>
            </details>
        </x-card>

        <!-- Item pickers -->
        @forelse($resume->sections->sortBy('sort_order') as $section)
            <x-card>
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h4 class="font-heading text-base font-semibold tracking-tight text-ink-900 uppercase">{{ $section->section_type }}</h4>
                        <p class="mt-1 text-xs text-slate-500">{{ $section->is_visible ? __('Visible in output') : __('Hidden — will not appear in preview/PDF') }} • {{ $section->items->count() }} selected</p>
                    </div>
                    <x-badge :variant="$section->is_visible ? 'success' : 'neutral'">{{ $section->is_visible ? __('Visible') : __('Hidden') }}</x-badge>
                </div>

                <!-- Selected items -->
                <div class="mb-6">
                    <h5 class="text-xs font-semibold tracking-widest uppercase text-slate-500 mb-3">Selected — ordered</h5>
                    @forelse($section->items->sortBy('sort_order') as $item)
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:border-ink-200 transition-colors mb-2">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <span class="hidden sm:grid place-items-center w-7 h-7 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600 shrink-0">{{ $loop->iteration }}</span>
                                <span class="text-sm text-ink-900 truncate">
                                    {{ class_basename($item->itemable_type) }} #{{ $item->itemable_id }} — {{ $item->itemable->job_title ?? $item->itemable->qualification ?? $item->itemable->name ?? \Illuminate\Support\Str::limit($item->itemable->content ?? 'Item', 48) }}
                                    <span class="ml-2 text-xs text-slate-400">[order {{ $item->sort_order }}]</span>
                                </span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <!-- Up/down for accessibility/mobile -->
                                <span class="hidden sm:flex items-center gap-1 text-xs text-slate-400 mr-2">↕ reorder</span>
                                <x-confirm-delete :action="route('resume-items.destroy', $item)" label="Remove" confirmTitle="Remove item?" />
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 italic bg-slate-50 border border-dashed border-slate-200 rounded-xl px-4 py-3">No items yet. Pick from your profile below.</p>
                    @endforelse

                    @if($section->items->isNotEmpty())
                        <details class="mt-4">
                            <summary class="cursor-pointer text-xs font-medium text-slate-600 hover:text-ink-900">Reorder items</summary>
                            <form method="POST" action="{{ route('resume-items.reorder') }}" class="mt-2 flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="ordered_ids" value="{{ $section->items->sortBy('sort_order')->pluck('id')->implode(',') }}" class="flex-1 rounded-lg border-slate-200 bg-white text-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100" placeholder="comma separated ids — e.g. 12,5,9">
                                <x-button type="submit" variant="secondary" size="sm">Apply order</x-button>
                            </form>
                            <p class="mt-1 text-xs text-slate-500">Tip: use the order field or drag handle (soon) — this input is the accessible fallback.</p>
                        </details>
                    @endif
                </div>

                <!-- Picker -->
                <div class="pt-5 border-t border-slate-100">
                    <h5 class="text-xs font-semibold tracking-widest uppercase text-slate-500 mb-3">Add from your profile</h5>
                    @php
                        $type = $section->section_type;
                        $candidates = collect();
                        $morph = '';
                        if ($type === 'experience') { $candidates = $experiences; $morph = 'App\Models\Experience'; }
                        elseif ($type === 'education') { $candidates = $educations; $morph = 'App\Models\Education'; }
                        elseif ($type === 'skills') { $candidates = $skills; $morph = 'App\Models\Skill'; }
                        elseif ($type === 'projects') { $candidates = $projects; $morph = 'App\Models\Project'; }
                    @endphp

                    @if($candidates->isEmpty())
                        <p class="text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-xl px-4 py-3">No {{ $type }} records in your profile yet. <a href="{{ $type === 'experience' ? route('experiences.index') : ($type === 'education' ? route('educations.index') : ($type === 'skills' ? route('skills.index') : route('projects.index'))) }}" class="font-medium text-ink-700 hover:underline">Add them first</a>.</p>
                    @else
                        <div class="grid gap-2.5">
                            @foreach($candidates as $candidate)
                                @php $already = $section->items->contains(fn($i) => $i->itemable_type === $morph && $i->itemable_id === $candidate->id); @endphp
                                <div class="flex items-center justify-between gap-3 p-3 rounded-xl border {{ $already ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-slate-200 hover:border-ink-200 hover:shadow-sm' }} transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-ink-900 truncate">
                                            @if($type==='experience') {{ $candidate->job_title }} <span class="font-normal text-slate-500">— {{ $candidate->organization }}</span>
                                            @elseif($type==='education') {{ $candidate->qualification }} <span class="font-normal text-slate-500">— {{ $candidate->institution }}</span>
                                            @elseif($type==='skills') {{ $candidate->name }} <span class="font-normal text-slate-500 text-xs">({{ $candidate->category }})</span>
                                            @elseif($type==='projects') {{ $candidate->name }}
                                            @endif
                                        </div>
                                        @if($already)<div class="text-xs font-medium text-emerald-700 mt-1">✓ already added to this section</div>@endif
                                    </div>
                                    @if(!$already)
                                        <form method="POST" action="{{ route('resumes.items.store', $resume) }}" class="shrink-0">
                                            @csrf
                                            <input type="hidden" name="section_type" value="{{ $type }}">
                                            <input type="hidden" name="itemable_type" value="{{ $morph }}">
                                            <input type="hidden" name="itemable_id" value="{{ $candidate->id }}">
                                            <x-button type="submit" variant="primary" size="sm" class="!px-3 !py-1.5">Add</x-button>
                                        </form>
                                    @else
                                        <span class="shrink-0 text-xs font-medium text-emerald-700 bg-white border border-emerald-200 rounded-full px-3 py-1">Added</span>
                                    @endif
                                </div>
                                @if($type==='experience' && $candidate->achievements->isNotEmpty())
                                    <div class="ml-4 sm:ml-6 space-y-1.5 -mt-1 mb-2">
                                        @foreach($candidate->achievements as $ach)
                                            @php $achMorph = 'App\Models\ExperienceAchievement'; $achAlready = $section->items->contains(fn($i) => $i->itemable_type===$achMorph && $i->itemable_id===$ach->id); @endphp
                                            <div class="flex items-center justify-between gap-2 pl-3 pr-2 py-2 rounded-lg border-l-2 {{ $achAlready ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-slate-200 hover:border-ink-200' }} transition-colors">
                                                <span class="text-xs text-slate-700 flex-1 min-w-0 truncate">↳ {{ \Illuminate\Support\Str::limit($ach->content, 64) }}</span>
                                                @if(!$achAlready)
                                                    <form method="POST" action="{{ route('resumes.items.store', $resume) }}" class="shrink-0">
                                                        @csrf
                                                        <input type="hidden" name="section_type" value="{{ $type }}">
                                                        <input type="hidden" name="itemable_type" value="{{ $achMorph }}">
                                                        <input type="hidden" name="itemable_id" value="{{ $ach->id }}">
                                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-white border border-slate-200 rounded-full hover:bg-ink-900 hover:text-white hover:border-ink-900 transition-colors">Add</button>
                                                    </form>
                                                @else
                                                    <span class="text-xs font-medium text-emerald-700">✓</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-4">
                    <p class="text-sm text-slate-600">No sections yet. Add one above to start curating.</p>
                    <p class="mt-1 text-xs text-slate-500">Sections you add will appear here for item selection.</p>
                </div>
            </x-card>
        @endforelse

        <x-card class="bg-ink-900 border-ink-900 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h4 class="font-heading text-sm font-semibold text-white">Ready to see the result?</h4>
                    <p class="mt-1 text-sm text-slate-300">Preview shows exactly what the PDF will contain.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-button href="{{ route('resumes.preview', $resume) }}" target="_blank" variant="secondary" class="!bg-white !text-ink-900 !border-white hover:!bg-slate-100">Preview</x-button>
                    <form method="POST" action="{{ route('resumes.export', $resume) }}" class="inline">
                        @csrf
                        <x-button type="submit" variant="honey">Export PDF</x-button>
                    </form>
                </div>
            </div>
        </x-card>

        <div class="flex flex-col sm:flex-row gap-3">
            <x-button href="{{ route('resumes.index') }}" variant="ghost" class="justify-center sm:justify-start">Back to resumes</x-button>
            <div class="flex-1 hidden sm:block"></div>
            <a href="{{ route('resumes.show', $resume) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-ink-700 hover:bg-slate-50">View summary</a>
        </div>
    </div>
</x-app-layout>
