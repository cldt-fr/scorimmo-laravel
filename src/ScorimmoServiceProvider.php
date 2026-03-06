<?php

namespace CLDT\Scorimmo;

use CLDT\Scorimmo\Http\Controllers\ScorimmoWebhooksController;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScorimmoServiceProvider extends PackageServiceProvider
{
    public function registeringPackage()
    {
        $this->app->bind('scorimmo', function () {
            return new Scorimmo();
        });

        parent::registeringPackage();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('scorimmo')
            ->hasConfigFile()
            ->hasMigration('create_scorimmo_webhook_calls_table');
    }

    public function bootingPackage()
    {
        $webhookPath = config('scorimmo.webhook_path');

        Route::post($webhookPath, ScorimmoWebhooksController::class);
    }
}
