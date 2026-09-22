<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="font-heading text-2xl font-semibold tracking-tight text-ink-900">Welcome back</h1>
        <p class="mt-2 text-sm text-slate-600">Sign in to continue building your career profile.</p>
    </div>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="mt-1.5 block w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-ink-700 hover:text-ink-900 hover:underline" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <x-text-input wire:model="form.password" id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <label for="remember" class="flex items-center gap-3 py-1 cursor-pointer group">
            <input wire:model="form.remember" id="remember" type="checkbox" class="w-4.5 h-4.5 rounded-md border-slate-300 text-ink-800 focus:ring-ink-200 focus:ring-4" name="remember">
            <span class="text-sm text-slate-700 group-hover:text-ink-900">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-full justify-center !py-3 text-[15px]">
            {{ __('Log in') }}
        </x-primary-button>

        <p class="text-center text-sm text-slate-600">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" wire:navigate class="font-medium text-ink-800 hover:text-ink-900 hover:underline">{{ __('Create one') }}</a>
        </p>
    </form>
</div>
