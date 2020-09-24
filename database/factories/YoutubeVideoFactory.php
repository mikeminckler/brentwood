<?php

namespace Database\Factories;

use App\Models\YoutubeVideo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class YoutubeVideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = YoutubeVideo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'video_id' => '1tW0Zj3YoJ4',
            'title' => $this->faker->sentence,
            'full_width' => false,
        ];
    }

    public function text()
    {
        return $this->state([
            'header' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
        ]);
    }
}
