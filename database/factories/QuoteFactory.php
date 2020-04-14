<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Quote;
use Faker\Generator as Faker;
use App\ContentElement;

$factory->define(Quote::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph,
        'author_name' => $faker->name,
        'author_details' => $faker->sentence,
    ];
});

$factory->afterCreating(Quote::class, function ($quote, $faker) {
    $content_element = factory(ContentElement::class)->create([
        'content_id' => $quote->id,
        'content_type' => get_class($quote),
    ]);
});
