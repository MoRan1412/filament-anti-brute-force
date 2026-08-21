<?php

namespace MoRan1412\FilamentAntiBruteForce;

use Filament\Contracts\Plugin;
use Filament\Panel;
use MoRan1412\FilamentAntiBruteForce\Pages\AntiBruteForce;

class FilamentAntiBruteForcePlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-anti-brute-force';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            AntiBruteForce::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
