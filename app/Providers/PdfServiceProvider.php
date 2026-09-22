<?php

namespace App\Providers;

use App\Services\Pdf\DompdfGenerator;
use App\Services\Pdf\PdfGeneratorInterface;
use Illuminate\Support\ServiceProvider;

class PdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PdfGeneratorInterface::class, function ($app) {
            $driver = config('pdf.driver', 'dompdf');

            return match ($driver) {
                'dompdf' => new DompdfGenerator(),
                // Future: 'browsershot' => new BrowsershotGenerator(),
                default => new DompdfGenerator(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
