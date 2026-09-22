<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Create your account</h1>
        <p class="mt-2 text-sm text-slate-600">Start building your professional profile in minutes.</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="mt-1.5 block w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="Alex Morgan" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="mt-1.5 block w-full" type="email" name="email" required autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="mt-1.5 text-xs text-slate-500">At least 8 characters.</p>
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="mt-1.5 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center !py-3 text-[15px]">
            {{ __('Register') }}
        </x-primary-button>

        <div class="relative flex items-center py-2">
            <div class="flex-grow border-t border-slate-200"></div>
            <span class="mx-4 flex-shrink text-xs font-medium uppercase tracking-widest text-slate-400">{{ __('or') }}</span>
            <div class="flex-grow border-t border-slate-200"></div>
        </div>

        <div class="space-y-2.5">
            <a href="{{ route('oauth.redirect', 'google') }}" class="flex w-full items-center justify-center gap-2.5 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:text-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink-200 focus-visible:ring-offset-2 transition-colors">
                <x-icons.google class="h-4 w-4 shrink-0" />
                {{ __('Continue with Google') }}
            </a>
            <a href="{{ route('oauth.redirect', 'linkedin') }}" class="flex w-full items-center justify-center gap-2.5 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:text-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink-200 focus-visible:ring-offset-2 transition-colors">
                <x-icons.linkedin class="h-4 w-4 shrink-0" />
                {{ __('Continue with LinkedIn') }}
            </a>
            <a href="{{ route('oauth.redirect', 'microsoft') }}" class="flex w-full items-center justify-center gap-2.5 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:text-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink-200 focus-visible:ring-offset-2 transition-colors">
                <x-icons.microsoft class="h-4 w-4 shrink-0" />
                {{ __('Continue with Microsoft') }}
            </a>
        </div>

        <p class="text-center text-sm text-slate-600">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-ink-800 hover:text-ink-900 hover:underline">{{ __('Sign in') }}</a>
        </p>
    </form>
</div>
