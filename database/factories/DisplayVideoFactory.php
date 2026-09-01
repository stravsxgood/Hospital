<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DisplayVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisplayVideo>
 */
class DisplayVideoFactory extends Factory
{
    /**
     * @var class-string<DisplayVideo>
     */
    protected $model = DisplayVideo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'file_path' => 'https://www.youtube.com/watch?v=zVgKnfN9i34',
            'order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
