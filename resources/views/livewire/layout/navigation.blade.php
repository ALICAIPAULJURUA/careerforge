<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="h-1 bg-gradient-to-r from-honey-600 via-honey-500 to-ink-800"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-[68px]">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 shrink-0">
                    <span class="w-9 h-9 rounded-xl bg-ink-900 text-white grid place-items-center font-heading font-bold text-[16px] tracking-tight shadow-sm">C</span>
                    <span class="hidden sm:block">
                        <span class="font-heading text-[17px] font-semibold tracking-tight text-ink-900 leading-none">CareerForge</span>
                        <span class="block text-[11px] font-medium tracking-widest uppercase text-slate-500 -mt-0.5">Professional Toolkit</span>
                    </span>
                </a>

                <div class="hidden lg:flex items-center gap-1.5">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('career-profile.edit')" :active="request()->routeIs('career-profile.*')" wire:navigate>
                        Profile
                    </x-nav-link>
                    <x-nav-link :href="route('experiences.index')" :active="request()->routeIs('experiences.*')" wire:navigate>
                        Experience
                    </x-nav-link>
                    <x-nav-link :href="route('educations.index')" :active="request()->routeIs('educations.*')" wire:navigate>
                        Education
                    </x-nav-link>
                    <x-nav-link :href="route('skills.index')" :active="request()->routeIs('skills.*')" wire:navigate>
                        Skills
                    </x-nav-link>
                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" wire:navigate>
                        Projects
                    </x-nav-link>
                    <x-nav-link :href="route('resumes.index')" :active="request()->routeIs('resumes.*')" wire:navigate>
                        Resumes
                    </x-nav-link>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Quick create -->
                <a href="{{ route('resumes.create') }}" wire:navigate class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full bg-honey-600 text-white text-sm font-medium hover:bg-honey-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>New Resume</span>
                </a>

                <x-dropdown align="right" width="64" contentClasses="py-0">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 pl-1 pr-3 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink-200">
                            <span class="w-8 h-8 rounded-full bg-ink-900 text-white grid place-items-center text-sm font-semibold">
                                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:block text-left">
                                <span class="block text-sm font-medium text-ink-900 leading-none" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"></span>
                                <span class="block text-xs text-slate-500 leading-none mt-0.5">{{ Str::limit(auth()->user()->email, 18) }}</span>
                            </span>
                            <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-4 border-b border-slate-100">
                            <p class="text-sm font-semibold text-ink-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"></p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="p-2 space-y-1">
                            <x-dropdown-link :href="route('career-profile.edit')" wire:navigate class="rounded-xl">
                                Career Profile
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('profile')" wire:navigate class="rounded-xl">
                                Account Settings
                            </x-dropdown-link>
                            <div class="my-1 border-t border-slate-100"></div>
                            <button wire:click="logout" class="w-full text-left">
                                <x-dropdown-link class="rounded-xl text-rose-700">
                                    Log Out
                                </x-dropdown-link>
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>

                <button @click="open = !open" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-ink-900 transition-colors">
                    <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile -->
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden border-t border-slate-200 bg-white">
        <div class="px-4 py-4 space-y-1 max-h-[70vh] overflow-auto">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('career-profile.edit')" :active="request()->routeIs('career-profile.*')" wire:navigate>Career Profile</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('experiences.index')" :active="request()->routeIs('experiences.*')" wire:navigate>Experience</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('educations.index')" :active="request()->routeIs('educations.*')" wire:navigate>Education</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('skills.index')" :active="request()->routeIs('skills.*')" wire:navigate>Skills</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" wire:navigate>Projects</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('resumes.index')" :active="request()->routeIs('resumes.*')" wire:navigate>Resumes</x-responsive-nav-link>
            <div class="pt-3 mt-3 border-t border-slate-100">
                <a href="{{ route('resumes.create') }}" wire:navigate class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-ink-900 text-white text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Resume
                </a>
            </div>
        </div>
    </div>
</nav>
