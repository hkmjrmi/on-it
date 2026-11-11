<?php

namespace Database\Factories;

use App\Enums\IssuePriority;
use App\Enums\IssueSource;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(IssueStatus::cases());
        $source = fake()->randomElement(IssueSource::cases());

        return [
            'title' => str(fake()->sentence(6))->rtrim('.')->value(),
            'description' => fake()->paragraphs(2, true),
            'type' => fake()->randomElement(IssueType::cases()),
            'priority' => fake()->randomElement(IssuePriority::cases()),
            'status' => $status,
            'source' => $source,
            'merchant_name' => $source === IssueSource::CustomerReport ? fake()->company() : null,
            'created_by' => User::factory(),
            'assigned_to' => $status === IssueStatus::New ? null : User::factory(),
            'released_at' => $status === IssueStatus::Released ? fake()->dateTimeBetween('-2 weeks') : null,
        ];
    }

    /**
     * Indicate that the issue has the given status.
     */
    public function status(IssueStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'released_at' => $status === IssueStatus::Released ? now() : null,
        ]);
    }

    /**
     * Indicate that the issue is not assigned.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => null,
        ]);
    }
}
