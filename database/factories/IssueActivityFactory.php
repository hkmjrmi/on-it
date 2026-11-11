<?php

namespace Database\Factories;

use App\Enums\IssueActivityAction;
use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IssueActivity>
 */
class IssueActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'issue_id' => Issue::factory(),
            'user_id' => User::factory(),
            'action' => IssueActivityAction::Created,
            'from_value' => null,
            'to_value' => null,
        ];
    }
}
