<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Photo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'alt' => $this->faker->sentence(5),
            'sort_order' => 1,
            'span' => 1,
            'offsetX' => 50,
            'offsetY' => 50,
            'fill' => true,
        ];
    }

    public function stat()
    {
        return $this->state([
            'stat_number' => $this->faker->randomNumber(2),
            'stat_name' => $this->faker->sentence,
        ]);
    }

    public function link()
    {
        return $this->state([
            'link' => Page::factory(),
        ]);
    }
}
