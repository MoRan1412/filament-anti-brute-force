<?php

namespace MoRan1412\FilamentAntiBruteForce\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MoRan1412\FilamentAntiBruteForce\FilamentAntiBruteForce
 */
class FilamentAntiBruteForce extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MoRan1412\FilamentAntiBruteForce\FilamentAntiBruteForce::class;
    }
}
