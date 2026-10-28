<?php

namespace Database\Factories;


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
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'), // null coalescing operator (ternary / elvis)
            'role' => $this->faker->randomElement(['ADMIN', 'CLIENT', 'COURIER']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
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

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'ADMIN',
        ]);
    }

    public  function courier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'COURIER',
        ]);
    }

    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'CLIENT',
        ]);
    }

    public function edho(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "superadmin",
            'email' => 'superadmin@gmail.com',  
            'password' => Hash::make('admin123'),
            // 'role' => 'ADMIN',
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function ainun(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Wiwik ainun janah",
            'email' => '230411100017@student.trunojoyo.ac.id',
            'password' => Hash::make('admin123'),
            'role' => 'ADMIN',
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function khoir(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Siti Khoirul Muzaro'ah",
            'email' => '230411100068@student.trunojoyo.ac.id',
            'password' => Hash::make('admin123'),
            'role' => 'ADMIN',
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function zulfri(): static {
        return $this->state(fn(array $attributes) => [
            'username' => "Zulfri",
            'email' => 'zulfri@gmail.com',
            'password' => Hash::make('zulfri123'),
            'role' => 'CLIENT',
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function seinal(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Seinal Arifin",
            'email' => '230411100149@student.trunojoyo.ac.id',
            'password' => Hash::make('admin123'),
            'role' => 'ADMIN',
            'avatar' => null,
            'status' => 'inactive',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function yichang(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Abd. Hamid Rizal",
            'email' => '230411100143@student.trunojoyo.ac.id',
            'password' => Hash::make('admin123'),
            'role' => 'ADMIN',
            'avatar' => null,
            'status' => 'inactive',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }

    public function roni(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Roni Finanda",
            'email' => '230411100025@student.trunojoyo.ac.id',
            'password' => Hash::make('courier123'),
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function prasetyo(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Dicky Prasetyo",
            'email' => '230411100166@student.trunojoyo.ac.id',
            'password' => Hash::make('courier123'),
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function nur(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Nur Jannah",
            'email' => '230411100038@student.trunojoyo.ac.id',
            'password' => Hash::make('client123'),
            'role' => 'CLIENT',
            'avatar' => null,
            'status' => 'inactive',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function suhaila(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "St Suhaila",
            'email' => '230411100037@student.trunojoyo.ac.id',
            'password' => Hash::make('client123'),
            'role' => 'CLIENT',
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
    public function layli(): static {
        return $this->state(fn (array $attributes) => [
            'username' => "Rahmatul layli",
            'email' => '230411100185@student.trunojoyo.ac.id',
            'password' => Hash::make('client123'),
            'role' => 'CLIENT',
            'avatar' => null,
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
}
