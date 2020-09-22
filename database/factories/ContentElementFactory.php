<?php

namespace Database\Factories;

use App\Models\ContentElement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Version;
use App\Models\Page;
use App\Models\Blog;

class ContentElementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentElement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
        ];
    }

    public function page()
    {
        return $this->state([
            'version_id' => Version::factory()->for(Page::factory(), 'versionable')->create(),
        ]);
    }

    public function blog()
    {
        return $this->state([
            'version_id' => Version::factory()->for(Blog::factory(), 'versionable')->create(),
        ]);
    }
}
