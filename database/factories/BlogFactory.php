<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Version;

class BlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Blog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName.$this->faker->randomNumber(3),
            'title' => $this->faker->lastName,
            'author' => $this->faker->firstName.' '.$this->faker->lastName,
            'unlisted' => 0,
        ];
    }

    public function published()
    {
        return $this->state([
            'published_version_id' => Version::factory()->published()->for(Blog::factory(), 'versionable'),
        ]);
    }

    public function unpublished()
    {
        return $this->state([
            'published_version_id' => null,
        ]);
    }

    public function unlisted()
    {
        return $this->state([
            'unlisted' => 1,
        ]);
    }
}
