<x-app-layout>
    <x-slot name="header">
        <div class="flex items-baseline gap-3">
            <h2 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Account Settings</h2>
            <span class="hidden sm:inline text-sm text-slate-500">— profile & security</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-page-header
            title="Profile"
            description="Manage your account information and password. Your career data lives under Career Profile."
        />

        <x-card>
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </x-card>

        <x-card>
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </x-card>

        <x-card class="border-rose-200">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </x-card>
    </div>
</x-app-layout>
