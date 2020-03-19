<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Photo;
use Faker\Generator as Faker;
use App\FileUpload;
use App\PhotoBlock;

$factory->define(Photo::class, function (Faker $faker) {
    return [
        'photo_block_id' => factory(PhotoBlock::class)->create()->id,
        'name' => $faker->name,
        'description' => $faker->sentence,
        'alt' => $faker->sentence(5),
        'sort_order' => 1,
        'span' => 1,
        'offsetX' => 50,
        'offsetY' => 50,
    ];
});

$factory->afterCreating(Photo::class, function ($photo, $faker) {
    $photo->fileUpload()->save(factory(FileUpload::class)->states('jpg')->create());
    $photo->load('fileUpload');
});
