<?php

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use MoRan1412\FilamentAntiBruteForce\FilamentAntiBruteForcePlugin;
use MoRan1412\FilamentAntiBruteForce\Models\LoginAttempt;
use MoRan1412\FilamentAntiBruteForce\Pages\AntiBruteForce;
use MoRan1412\FilamentAntiBruteForce\Tests\Support\TestUser;

beforeEach(function () {
    Carbon::setTestNow('2026-01-01 00:00:00');

    $panel = Panel::make()
        ->id('admin')
        ->default()
        ->plugin(FilamentAntiBruteForcePlugin::make());

    Filament::registerPanel($panel);
    Filament::setCurrentPanel($panel);

    $this->actingAs(new TestUser);
});

afterEach(function () {
    Carbon::setTestNow(null);
});

it('renders the blocked attempts page', function () {
    LoginAttempt::factory()->locked()->create([
        'ip' => '1.2.3.4',
        'email' => 'a@example.com',
    ]);

    Livewire::test(AntiBruteForce::class)
        ->assertSuccessful()
        ->assertSee('1.2.3.4');
});

it('can unblock a record', function () {
    $record = LoginAttempt::factory()->locked()->create();

    Livewire::test(AntiBruteForce::class)
        ->callTableAction('unblock', $record);

    $record->refresh();

    expect($record->isLocked())->toBeFalse()
        ->and($record->attempts)->toBe(0)
        ->and($record->lockout_count)->toBe(0);
});

it('can delete a record', function () {
    $record = LoginAttempt::factory()->locked()->create();

    Livewire::test(AntiBruteForce::class)
        ->callTableAction('delete', $record);

    expect(LoginAttempt::query()->find($record->id))->toBeNull();
});

it('denies access when the admin page is disabled', function () {
    config()->set('anti-brute-force.admin_page.enabled', false);

    Livewire::test(AntiBruteForce::class)
        ->assertStatus(403);
});

it('gates the page behind an ability when configured', function () {
    config()->set('anti-brute-force.admin_page.authorize', 'view-anti-brute-force');

    Gate::define('view-anti-brute-force', fn (): bool => false);

    Livewire::test(AntiBruteForce::class)
        ->assertStatus(403);

    Gate::define('view-anti-brute-force', fn (): bool => true);

    Livewire::test(AntiBruteForce::class)->assertSuccessful();
});

it('hides navigation when disabled', function () {
    config()->set('anti-brute-force.admin_page.enabled', false);

    expect(AntiBruteForce::shouldRegisterNavigation())->toBeFalse();
});
