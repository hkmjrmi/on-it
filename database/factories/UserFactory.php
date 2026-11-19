<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static string|null $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = str(fake()->unique()->firstName())->append(' ', fake()->lastName())->value();

        return [
            'name' => $name,
            'email' => str($name)->slug()->append('@', fake()->safeEmailDomain())->value(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('abcd1234'),
            'role' => fake()->randomElement(UserRole::cases()),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a programmer.
     */
    public function programmer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Programmer,
        ]);
    }

    /**
     * Indicate that the user is a coordinator.
     */
    public function coordinator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Coordinator,
        ]);
    }

    /**
     * Indicate that the user is a customer support.
     */
    public function customerSupport(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CustomerSupport,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be randomly verified.
     */
    public function randomlyVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => fake()->randomElement([null, now()]),
        ]);
    }
}
