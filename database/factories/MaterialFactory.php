<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->name();
        return [
            'title' => $title,
            'description' => $this->faker->text(),
            'content' => $this->faker->text(),
            'thumbnail' => $this->faker->imageUrl(640, 480, null, true),
            'slug' => Str::slug($title),
            'created_at' => Carbon::now()->subMonth(random_int(1, 12))
        ];
    }
}
