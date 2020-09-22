<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Version;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName.$this->faker->randomNumber(3),
            'parent_page_id' => 1,
            'sort_order' => $this->faker->randomNumber(1),
            'unlisted' => 0,
        ];
    }

    public function secondLevel()
    {
        return $this->state([
            'parent_page_id' => Page::factory(),
        ]);
    }

    public function slug()
    {
        return $this->state([
            'slug' => $this->faker->firstName,
        ]);
    }

    public function published()
    {
        return $this->state([
            'published_version_id' => Version::factory()->published()->for(Page::factory(), 'versionable'),
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
