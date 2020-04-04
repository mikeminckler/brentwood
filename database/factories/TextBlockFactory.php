<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\TextBlock;
use Faker\Generator as Faker;

use App\ContentElement;

$factory->define(TextBlock::class, function (Faker $faker) {
    return [
        'header' => $faker->sentence($faker->numberBetween(1,5)),
        'body' => $faker->paragraph,
        'style' => 'gray',
    ];
});

$factory->afterCreating(TextBlock::class, function ($text_block, $faker) {
    $content_element = factory(ContentElement::class)->create([
        'content_id' => $text_block->id,
        'content_type' => get_class($text_block),
    ]);
});
