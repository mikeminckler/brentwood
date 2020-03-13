<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Page;
use Faker\Generator as Faker;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName.$faker->randomNumber(3),
        'parent_page_id' => 1,
        'order' => $faker->randomNumber(1),
    ];
});

$factory->state(App\Page::class, 'secondLevel', function ($faker) {
    return [
        'parent_page_id' => factory(Page::class)->create()->id,
    ];
});

$factory->state(App\Page::class, 'slug', function ($faker) {
    return [
        'slug' => $faker->firstName,
    ];
});
