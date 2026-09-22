<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Dashboard</h2>
                <p class="mt-1 text-sm text-slate-600">Welcome back, {{ auth()->user()->name }} — here’s your career at a glance.</p>
            </div>
            <a href="{{ route('resumes.create') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 bg-ink-900 text-white rounded-full text-sm font-medium hover:bg-ink-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Resume
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Status -->
        <x-alert type="success" class="border-honey-200 bg-honey-50 text-ink-900">
            <span class="font-medium">{{ __("You're logged in!") }}</span>
            <span class="ml-2 text-slate-600">— all your data is private and ready to build from.</span>
        </x-alert>

        <!-- Stats grid -->
        @php
            $user = auth()->user();
            $counts = [
                ['label' => 'Experiences', 'value' => $user->experiences()->count(), 'href' => route('experiences.index'), 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['label' => 'Education', 'value' => $user->educations()->count(), 'href' => route('educations.index'), 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['label' => 'Skills', 'value' => $user->skills()->count(), 'href' => route('skills.index'), 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                ['label' => 'Projects', 'value' => $user->projects()->count(), 'href' => route('projects.index'), 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['label' => 'Resumes', 'value' => $user->resumes()->count(), 'href' => route('resumes.index'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ];
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($counts as $c)
                <a href="{{ $c['href'] }}" class="group bg-white border border-slate-200 rounded-2xl p-5 hover:border-ink-200 hover:shadow-soft transition-all duration-150">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 group-hover:bg-ink-900 group-hover:border-ink-900 group-hover:text-white flex items-center justify-center text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $c['icon'] }}"/></svg>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-heading font-semibold tracking-tight text-ink-900">{{ $c['value'] }}</div>
                        <div class="text-xs font-medium tracking-widest uppercase text-slate-500">{{ $c['label'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Two columns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Quick actions -->
            <x-card class="lg:col-span-1">
                <h3 class="font-heading text-base font-semibold text-ink-900">Quick actions</h3>
                <p class="mt-1 text-sm text-slate-600">Jump to what needs attention.</p>
                <div class="mt-5 space-y-2.5">
                    <a href="{{ route('career-profile.edit') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:border-ink-200 hover:bg-slate-50 transition-colors group">
                        <span class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-honey-50 border border-honey-100 text-honey-700 grid place-items-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                            <span class="text-sm font-medium text-ink-900">Update profile</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-ink-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('experiences.create') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 hover:border-ink-200 hover:bg-slate-50 transition-colors group">
                        <span class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 grid place-items-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></span>
                            <span class="text-sm font-medium text-ink-900">Add experience</span>
                        </span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-ink-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('resumes.create') }}" class="flex items-center justify-between p-3.5 rounded-xl bg-ink-900 text-white hover:bg-ink-800 transition-colors">
                        <span class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 grid place-items-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></span>
                            <span class="text-sm font-medium">Create resume</span>
                        </span>
                        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </x-card>

            <!-- Recent resumes -->
            <x-card class="lg:col-span-2">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-heading text-base font-semibold text-ink-900">Recent resumes</h3>
                        <p class="mt-1 text-sm text-slate-600">Your curated documents — ready to preview or export.</p>
                    </div>
                    <a href="{{ route('resumes.index') }}" class="text-xs font-medium text-ink-700 hover:text-ink-900 hover:underline">View all →</a>
                </div>

                @php $recent = auth()->user()->resumes()->with(['template','theme'])->latest()->take(3)->get(); @endphp
                <div class="mt-6">
                    @forelse($recent as $r)
                        <a href="{{ route('resumes.edit', $r) }}" class="flex items-center justify-between py-3.5 border-b border-slate-100 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded-xl transition-colors">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-ink-900 truncate">{{ $r->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $r->template->name ?? 'Modern' }} • {{ $r->theme->name ?? 'Default' }} @if($r->target_role) • {{ $r->target_role }} @endif</div>
                            </div>
                            <span class="shrink-0 ml-4 text-xs font-medium text-slate-500">{{ $r->created_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <x-empty-state title="No resumes yet" description="Create your first tailored resume from your profile." class="mt-2">
                            <x-button href="{{ route('resumes.create') }}" variant="primary" size="sm">Create resume</x-button>
                        </x-empty-state>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
