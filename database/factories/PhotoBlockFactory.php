<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PhotoBlock;
use Faker\Generator as Faker;
use App\Photo;

$factory->define(PhotoBlock::class, function (Faker $faker) {
    return [
        'columns' => 1,
        'height' => 33,
        'padding' => false,
        'show_text' => false,
    ];
});

/*
$factory->afterCreating(PhotoBlock::class, function ($photo_block, $faker) {
    $photo = factory(Photo::class)->create([
        'photo_block_id' => $photo_block->id,
    ]);
});
 */

$factory->state(PhotoBlock::class, 'with-text', function ($faker) {
    return [
        'header' => $faker->sentence,
        'body' => $faker->paragraph,
        'text_order' => 1,
        'text_span' => 1,
    ];
});
