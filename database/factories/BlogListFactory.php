<?php

namespace Database\Factories;

use App\Models\BlogList;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BlogList::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'header' => null,
        ];
    }
}
