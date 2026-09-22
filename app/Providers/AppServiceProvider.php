<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->guardDestructiveCommands();
        }

        $this->registerSocialiteProviders();
    }

    protected function registerSocialiteProviders(): void
    {
        // LinkedIn & Microsoft are not in Socialite core; use socialiteproviders/* (MIT, free).
        // Google is built-in and needs no extension. Apple is intentionally skipped:
        // "Sign in with Apple" requires $99/year Apple Developer Program enrollment (account-level fee),
        // failing the "free to add only" requirement, even though the API itself is free.
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\LinkedIn\LinkedInExtendSocialite::class.'@handle'
        );
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle'
        );
    }

    /**
     * Prevent accidental data loss from destructive artisan commands against the
     * real local database file. In the phpunit / testing context (APP_ENV=testing
     * + sqlite :memory: via phpunit.xml) commands run without --force. Outside
     * that context, destructive commands require --force and always emit a
     * warning naming the resolved DB_DATABASE path.
     */
    protected function guardDestructiveCommands(): void
    {
        // Keep in sync with docs/OPEN-DECISIONS.md entry 15.
        $destructive = [
            'migrate:fresh',
            'migrate:reset',
            'migrate:refresh',
            'migrate:rollback',
            'db:wipe',
        ];

        Event::listen(CommandStarting::class, function (CommandStarting $event) use ($destructive) {
            $command = $event->command ?? '';
            // $event->command may be null for closure commands; fall back to input name
            if ($command === '' || $command === null) {
                $command = $event->input->getFirstArgument() ?? '';
                // For artisan commands the name is the first argument, but for
                // named commands it's in the input's argument; fallback to raw string
                if ($command === '') {
                    $command = (string) $event->input;
                }
            }

            // Normalize: Artisan passes the command name as e.g. "migrate:fresh"
            $baseCommand = explode(' ', trim($command))[0];

            if (! in_array($baseCommand, $destructive, true)) {
                return;
            }

            // Trusted test context: phpunit.xml sets APP_ENV=testing + sqlite :memory:
            // In that context RefreshDatabase / artisan test must run without --force.
            $isTestingEnv = $this->app->environment('testing');
            $dbConnection = config('database.default');
            $dbDatabase = config("database.connections.{$dbConnection}.database");

            // Resolve display path for warning — never expose password, just DB name/path.
            $displayDatabase = $dbDatabase;
            if ($displayDatabase === ':memory:') {
                $displayDatabase = ':memory: (phpunit.xml in-memory SQLite)';
            } elseif ($displayDatabase === null || $displayDatabase === '') {
                $displayDatabase = env('DB_DATABASE', '(not set)');
            }

            // Allow testing context unconditionally (php artisan test).
            if ($isTestingEnv) {
                // Still emit informational warning when --force is not needed in tests
                // is optional; we stay silent in testing to keep phpunit output clean.
                return;
            }

            $hasForce = (bool) $event->input->hasParameterOption(['--force', '-f']);

            $warning = sprintf(
                'WARNING: "%s" targets the real local database "%s" (connection "%s", APP_ENV="%s"). '
                . 'This will drop/truncate tables and can permanently delete users/career data. '
                . 'Back up database/database.sqlite first (e.g. copy to database/database.sqlite.bak).',
                $baseCommand,
                $displayDatabase,
                $dbConnection,
                $this->app->environment()
            );

            if (! $hasForce) {
                // Output to both event output and STDERR so it is visible even when
                // the command is aborted before its own output is initialized.
                if (isset($event->output)) {
                    $event->output->writeln("<error>Refusing to run \"{$baseCommand}\" without --force.</error>");
                    $event->output->writeln("<comment>{$warning}</comment>");
                    $event->output->writeln("<comment>Re-run with --force to confirm you have a backup and intend to wipe \"{$displayDatabase}\".</comment>");
                }
                // Also write to STDERR for non-interactive callers
                fwrite(STDERR, "Refusing to run \"{$baseCommand}\" without --force.\n{$warning}\nRe-run with --force to confirm.\n");

                // Abort execution — exit code 1 signals failure to caller / CI.
                // Throwing prevents the underlying Symfony command from running.
                throw new \RuntimeException("Destructive command \"{$baseCommand}\" requires --force outside testing (:memory:) context. Target DB: {$displayDatabase}");
            }

            // --force supplied outside testing: still warn loudly so the DB path is not missed
            if (isset($event->output)) {
                $event->output->writeln("<comment>{$warning}</comment>");
                $event->output->writeln("<comment>Continuing because --force was supplied. Target: {$displayDatabase}</comment>");
            }
        });
    }
}
