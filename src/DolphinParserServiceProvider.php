<?php

namespace SimoneBianco\DolphinParser;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class DolphinParserServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('dolphin-parser')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('simonebianco/laravel-dolphin-parser');
            });
    }

    /**
     * Register bindings in the container.
     */
    public function packageRegistered(): void
    {
        // Bind the client as a singleton
        $this->app->singleton('dolphin-parser', function ($app) {
            return new DolphinParserClient();
        });

        // Also bind the class itself for dependency injection
        $this->app->singleton(DolphinParserClient::class, function ($app) {
            return $app->make('dolphin-parser');
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'dolphin-parser',
            DolphinParserClient::class,
        ];
    }
}
