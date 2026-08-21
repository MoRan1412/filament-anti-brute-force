<?php

namespace MoRan1412\FilamentAntiBruteForce;

use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use MoRan1412\FilamentAntiBruteForce\Commands\CleanupCommand;
use MoRan1412\FilamentAntiBruteForce\Listeners\AuthenticationListener;
use MoRan1412\FilamentAntiBruteForce\Testing\TestsFilamentAntiBruteForce;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentAntiBruteForceServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-anti-brute-force';

    public static string $viewNamespace = 'filament-anti-brute-force';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasConfigFile('anti-brute-force')
            ->hasMigrations($this->getMigrations())
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('moran1412/filament-anti-brute-force');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AntiBruteForceService::class);
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-anti-brute-force/{$file->getFilename()}"),
                ], 'filament-anti-brute-force-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentAntiBruteForce);

        // Brute force protection listeners
        $events = $this->app->make(Dispatcher::class);

        $events->listen(Attempting::class, [AuthenticationListener::class, 'handleAttempting']);
        $events->listen(Failed::class, [AuthenticationListener::class, 'handleFailed']);
        $events->listen(Login::class, [AuthenticationListener::class, 'handleLogin']);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'moran1412/filament-anti-brute-force';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            CleanupCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_anti_brute_force_table',
        ];
    }
}
