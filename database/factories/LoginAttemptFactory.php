<?php

namespace MoRan1412\FilamentAntiBruteForce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MoRan1412\FilamentAntiBruteForce\Models\LoginAttempt;

class LoginAttemptFactory extends Factory
{
    protected $model = LoginAttempt::class;

    public function definition(): array
    {
        return [
            'ip' => fake()->ipv4(),
            'email' => fake()->safeEmail(),
            'attempts' => 0,
            'lockout_count' => 0,
            'locked_until' => null,
            'last_attempt_at' => now(),
        ];
    }

    public function locked(): static
    {
        return $this->state(fn (): array => [
            'attempts' => (int) config('anti-brute-force.max_attempts'),
            'lockout_count' => 1,
            'locked_until' => now()->addMinutes((int) config('anti-brute-force.lockout_minutes')),
        ]);
    }
}
