<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Page;
use Faker\Generator as Faker;

use App\Version;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName.$faker->randomNumber(3),
        'parent_page_id' => 1,
        'sort_order' => $faker->randomNumber(1),
    ];
});

$factory->state(Page::class, 'secondLevel', function ($faker) {
    return [
        'parent_page_id' => factory(Page::class)->create()->id,
    ];
});

$factory->state(Page::class, 'slug', function ($faker) {
    return [
        'slug' => $faker->firstName,
    ];
});

$factory->state(Page::class, 'published', function ($faker) {
    return [
        'published_version_id' => factory(Version::class)->states('published')->create()->id,
    ];
});

$factory->state(Page::class, 'unpublished', function ($faker) {
    return [
        'published_version_id' => null,
    ];
});

$factory->state(Page::class, 'unlisted', function ($faker) {
    return [
        'unlisted' => 1,
    ];
});
