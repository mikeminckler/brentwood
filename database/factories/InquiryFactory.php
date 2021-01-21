<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Facades\URL;

class InquiryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->numberBetween(2501000000, 6041000000),
            'target_grade' => $this->faker->numberBetween(8, 12),
            'target_year' => now()->format('Y'),
            'student_type' => $this->faker->randomElement(['day', 'boarding']),
        ];
    }

    public function configure() 
    {
        return $this->afterCreating(function ($inquiry) {
            $inquiry->url = URL::signedRoute('inquiries.view', ['id' => $inquiry->id]);
            $inquiry->save();
        });
    }
    
}
