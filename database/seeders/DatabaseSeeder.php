<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::firstOrCreate([
            'id' => 1,
        ], [
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'email_verified_at' => now(),
            'password' => 'abcd1234',
        ]);

        User::firstOrCreate([
            'id' => 1,
        ], [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'email_verified_at' => now(),
            'password' => 'abcd1234',
            'role' => UserRole::CustomerSupport,
        ]);

        User::firstOrCreate([
            'email' => 'programmer@test.com',
        ], [
            'name' => 'Test Programmer',
            'email_verified_at' => now(),
            'password' => 'abcd1234',
            'role' => UserRole::Programmer,
        ]);

        if (app()->isLocal()) {
            $this->call(IssueSeeder::class);
        }
    }
}
