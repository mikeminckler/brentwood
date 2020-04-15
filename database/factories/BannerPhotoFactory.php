<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\BannerPhoto;
use Faker\Generator as Faker;
use App\ContentElement;

$factory->define(BannerPhoto::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph,
        'header' => $faker->sentence,
    ];
});

$factory->afterCreating(BannerPhoto::class, function ($banner_photo, $faker) {
    $content_element = factory(ContentElement::class)->create([
        'content_id' => $banner_photo->id,
        'content_type' => get_class($banner_photo),
    ]);
});
