<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OauthController extends Controller
{
    public const ALLOWED_PROVIDERS = ['google', 'linkedin', 'microsoft'];

    public function redirect(string $provider): RedirectResponse
    {
        $provider = strtolower($provider);

        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $provider = strtolower($provider);

        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        $socialUser = Socialite::driver($provider)->user();

        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?: $socialUser->getNickname() ?: 'User';
        // Provider may return full user array in $socialUser->user
        $raw = $socialUser->user ?? [];

        // 1. Existing oauth link -> log in directly
        $oauth = OauthProvider::where('provider', $provider)
            ->where('provider_id', (string) $providerId)
            ->first();

        if ($oauth) {
            Auth::login($oauth->user);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        // 2. No oauth link -> check email-match for linking (only if verified)
        $isVerified = $this->isEmailVerified($socialUser, $provider, $raw);

        if ($email && $isVerified) {
            $existingUser = User::where('email', strtolower($email))->first();

            if ($existingUser) {
                // Link new provider to existing account (verified email only)
                $existingUser->oauthProviders()->create([
                    'provider' => $provider,
                    'provider_id' => (string) $providerId,
                    'provider_email' => $email,
                ]);

                // If provider says email verified and user's email not yet verified, mark it
                if (! $existingUser->email_verified_at) {
                    $existingUser->forceFill(['email_verified_at' => now()])->save();
                }

                Auth::login($existingUser);
                request()->session()->regenerate();

                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

        // 3. No existing user -> create new user with password = null
        // If email is taken but not verified, we must not collide; generate placeholder
        $emailForCreation = $email;
        if ($emailForCreation && ! $isVerified) {
            // Do not reuse unverified email for new account if it already exists
            $exists = User::where('email', strtolower($emailForCreation))->exists();
            if ($exists) {
                // Fallback to provider-specific placeholder to avoid unique violation
                $emailForCreation = $provider.'_'.Str::slug((string) $providerId).'@'.parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.invalid';
                // Ensure unique
                $base = $emailForCreation;
                $i = 1;
                while (User::where('email', $emailForCreation)->exists()) {
                    $emailForCreation = $i.'_'.$base;
                    $i++;
                }
            }
        }
        if (! $emailForCreation) {
            $emailForCreation = $provider.'_'.Str::slug((string) $providerId).'@'.parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.invalid';
        }

        // Ensure unique email for creation
        $finalEmail = $emailForCreation;
        if (User::where('email', strtolower((string) $finalEmail))->exists()) {
            // Extremely unlikely after above handling, but guard anyway
            $finalEmail = Str::slug($provider).'_'.time().'_'.Str::random(4).'@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.invalid');
        }

        $user = User::create([
            'name' => $name,
            'email' => strtolower((string) $finalEmail),
            'password' => null,
            'email_verified_at' => $isVerified && $email ? now() : null,
        ]);

        $user->oauthProviders()->create([
            'provider' => $provider,
            'provider_id' => (string) $providerId,
            'provider_email' => $email,
        ]);

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Determine whether the provider reports the email as verified.
     * Google, Microsoft (Entra ID) and LinkedIn all return a verified flag;
     * we only auto-link on verified email to prevent account takeover.
     */
    protected function isEmailVerified(mixed $socialUser, string $provider, mixed $raw): bool
    {
        // Laravel Socialite's AbstractUser exposes getEmail() but not verified; check raw payload
        if (is_array($raw)) {
            // Google
            if (array_key_exists('email_verified', $raw)) {
                return (bool) $raw['email_verified'];
            }
            if (array_key_exists('verified_email', $raw)) {
                return (bool) $raw['verified_email'];
            }
            // LinkedIn OpenID
            if (array_key_exists('email_verified', $raw) && is_bool($raw['email_verified'])) {
                return $raw['email_verified'];
            }
            // Microsoft Graph: email comes via `mail` or `userPrincipalName`; verified via `email` presence and `verified`? Microsoft generally considers Entra ID email verified
            // For Microsoft we treat presence of email as verified, unless explicitly marked
            if ($provider === 'microsoft') {
                // Microsoft's socialite provider returns decoded id_token claims; check `email_verified` if present
                if (array_key_exists('email_verified', $raw)) {
                    return (bool) $raw['email_verified'];
                }
                // If we have an email from Microsoft, consider it verified (Entra ID)
                if (! empty($raw['mail']) || ! empty($raw['userPrincipalName']) || ! empty($raw['email'])) {
                    return true;
                }
            }
        }

        // Fallback: Socialite may store verified via object property (mocked tests)
        if (is_object($socialUser) && property_exists($socialUser, 'user') && is_array($socialUser->user)) {
            // already handled
        }

        // For Google/LinkedIn/Microsoft, if email is present and no explicit verified flag, assume verified for Google/Microsoft
        // but for safety, LinkedIn without flag should not auto-link; we check provider
        if ($provider === 'google' || $provider === 'microsoft') {
            return $socialUser->getEmail() !== null;
        }

        // For LinkedIn, require explicit verified flag; if missing, do not auto-link
        if ($provider === 'linkedin') {
            // SocialiteProviders LinkedIn OpenID returns email_verified in raw; if missing, treat as unverified
            return false;
        }

        return false;
    }
}
