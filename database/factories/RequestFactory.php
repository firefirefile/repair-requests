<?php

namespace Database\Factories;

use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFactory extends Factory
{
    protected $model = Request::class;

    public function definition(): array
    {
        return [
            'client_name' => $this->faker->name(),
            'clientName' => $this->faker->name(), // дубль для camelCase
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'problem_text' => $this->faker->paragraph(),
            'problemText' => $this->faker->paragraph(), // дубль для camelCase
            'status' => $this->faker->randomElement(['new', 'assigned', 'in_progress', 'done', 'canceled']),
            'assigned_to' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    public function newRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'assigned_to' => null,
        ]);
    }

    public function assignedRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'assigned_to' => User::factory()->create(['role' => 'master'])->id,
        ]);
    }
}
