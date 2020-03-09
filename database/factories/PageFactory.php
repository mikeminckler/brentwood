<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Page;
use Faker\Generator as Faker;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName.$faker->randomNumber(3),
        'parent_page_id' => null,
        'order' => $faker->randomNumber(1),
    ];
});

$factory->state(Page::class, 'child', [
]);

$factory->afterCreatingState(Page::class, 'child', function ($page, $faker) {
    $parent_page = factory(Page::class)->create();
    $page->parent_page_id = $parent_page->id;
    $page->save();
});
