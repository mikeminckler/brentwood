<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Photo;
use Faker\Generator as Faker;
use App\FileUpload;
use App\PhotoBlock;
use App\Quote;

$factory->define(Photo::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->sentence,
        'alt' => $faker->sentence(5),
        'sort_order' => 1,
        'span' => 1,
        'offsetX' => 50,
        'offsetY' => 50,
        'fill' => true,
    ];
});

$factory->afterCreating(Photo::class, function ($photo, $faker) {
    $photo->fileUpload()->save(factory(FileUpload::class)->states('jpg')->create());
    $photo->load('fileUpload');
});

$factory->state(Photo::class, 'photo-block', function ($faker) {
    $photo_block = factory(PhotoBlock::class)->create();
    return [
        'content_id' => $photo_block->id,
        'content_type' => get_class($photo_block),
    ];
});

$factory->state(Photo::class, 'quote', function ($faker) {
    $quote = factory(Quote::class)->create();
    return [
        'content_id' => $quote->id,
        'content_type' => get_class($quote),
    ];
});
