<?php

namespace Database\Factories;

use App\Models\Livestream;
use Illuminate\Database\Eloquent\Factories\Factory;

class LivestreamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Livestream::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'video_id' => 'wf5uwSu21io',
            'start_date' => now()->addMinutes($this->faker->numberBetween(10, 5000))->roundSecond(),
            'length' => $this->faker->numberBetween(10, 120),
            'enable_chat' => true,
        ];
    }
}
