<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Blog;
use App\Version;
use Faker\Generator as Faker;

$factory->define(Blog::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName.$faker->randomNumber(3),
        'author' => $faker->firstName.' '.$faker->lastName,
        'unlisted' => 0,
    ];
});

$factory->state(Blog::class, 'published', function ($faker) {
    return [
        'published_version_id' => factory(Version::class)->states('page', 'published')->create()->id,
    ];
});

$factory->state(Blog::class, 'unpublished', function ($faker) {
    return [
        'published_version_id' => null,
    ];
});

$factory->state(Blog::class, 'unlisted', function ($faker) {
    return [
        'unlisted' => 1,
    ];
});
