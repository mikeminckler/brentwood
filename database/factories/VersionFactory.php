<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Version;
use Faker\Generator as Faker;

use App\Page;

$factory->define(Version::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName,
        'page_id' => factory(Page::class)->create()->id,
    ];
});

$factory->state(Version::class, 'published', function ($faker) {
    return [
        'published_at' => now(),
    ];
});
