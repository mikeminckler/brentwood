<?php

namespace Database\Factories;

use App\Models\InquiryForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class InquiryFormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InquiryForm::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'header' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'show_student_info' => $this->faker->boolean(50),
            'show_interests' => $this->faker->boolean(50),
            'show_livestreams' => $this->faker->boolean(50),
            'show_livestreams_first' => $this->faker->boolean(50),
            'create_password' => $this->faker->boolean(50),
        ];
    }
}
