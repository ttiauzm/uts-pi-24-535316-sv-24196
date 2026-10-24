<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'director' => fake()->name(),
            'year' => fake()->numberBetween(1970, (int) date('Y')),
            'status' => fake()->randomElement(Film::STATUSES),
            'rating' => fake()->numberBetween(1, 5),
            'review' => fake()->paragraph(),
        ];
    }
}
