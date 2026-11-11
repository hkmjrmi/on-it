<?php

namespace Database\Factories;

use App\Models\Issue;
use App\Models\IssueAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IssueAttachment>
 */
class IssueAttachmentFactory extends Factory
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
            'disk' => 'local',
            'path' => 'issue-attachments/' . fake()->uuid() . '.png',
            'original_name' => fake()->word() . '.png',
            'mime_type' => 'image/png',
            'size_bytes' => fake()->numberBetween(10_000, 2_000_000),
        ];
    }
}
