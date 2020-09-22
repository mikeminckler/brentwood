<?php

namespace Database\Factories;

use App\Models\TextBlock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TextBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TextBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'header' => $this->faker->sentence($this->faker->numberBetween(1, 5)),
            'body' => $this->faker->paragraph,
            'style' => 'gray',
            'full_width' => false,
        ];
    }

    public function stat()
    {
        return $this->state([
            'stat_number' => $this->faker->randomNumber(2),
            'stat_name' => $this->faker->word,
        ]);
    }
}
