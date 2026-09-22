<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="font-heading text-xl font-semibold tracking-tight text-ink-900">Verify your email</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6">
            <x-alert type="success">{{ __('A new verification link has been sent to the email address you provided during registration.') }}</x-alert>
        </div>
    @endif

    <div class="space-y-3">
        <x-primary-button wire:click="sendVerification" class="w-full justify-center !py-3">
            {{ __('Resend Verification Email') }}
        </x-primary-button>

        <button wire:click="logout" type="submit" class="w-full text-center text-sm font-medium text-slate-600 hover:text-ink-900 underline-offset-4 hover:underline">
            {{ __('Log Out') }}
        </button>
    </div>
</div>
