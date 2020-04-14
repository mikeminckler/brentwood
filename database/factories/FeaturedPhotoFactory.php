<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\FeaturedPhoto;
use Faker\Generator as Faker;
use App\ContentElement;

$factory->define(FeaturedPhoto::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph,
        'header' => $faker->sentence,
    ];
});

$factory->afterCreating(FeaturedPhoto::class, function ($featured_photo, $faker) {
    $content_element = factory(ContentElement::class)->create([
        'content_id' => $featured_photo->id,
        'content_type' => get_class($featured_photo),
    ]);
});

