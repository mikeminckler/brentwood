<?php

namespace Database\Factories;

use App\Models\PhotoBlock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PhotoBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhotoBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'columns' => 1,
            'height' => 33,
            'padding' => false,
            'show_text' => false,
        ];
    }

    public function withText()
    {
        return $this->state([
            'header' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'text_order' => 1,
            'text_span' => 1,
            'text_style' => 1,
        ]);
    }
}
