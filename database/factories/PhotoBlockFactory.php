<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\PhotoBlock;
use Faker\Generator as Faker;
use App\Photo;
use App\ContentElement;

$factory->define(PhotoBlock::class, function (Faker $faker) {
    return [
        'columns' => 1,
        'height' => 33,
        'padding' => false,
        'show_text' => false,
    ];
});

$factory->afterCreating(PhotoBlock::class, function ($photo_block, $faker) {
    $content_element = factory(ContentElement::class)->states('page')->create([
        'content_id' => $photo_block->id,
        'content_type' => get_class($photo_block),
    ]);
});

$factory->state(PhotoBlock::class, 'with-text', function ($faker) {
    return [
        'header' => $faker->sentence,
        'body' => $faker->paragraph,
        'text_order' => 1,
        'text_span' => 1,
        'text_style' => 1,
    ];
});
