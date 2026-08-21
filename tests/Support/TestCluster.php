<?php

namespace MoRan1412\FilamentAntiBruteForce\Tests\Support;

use Filament\Clusters\Cluster;

class TestCluster extends Cluster
{
    protected static ?string $slug = 'test-cluster';
}
