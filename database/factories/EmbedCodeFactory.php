<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\EmbedCode;
use Faker\Generator as Faker;

use App\ContentElement;

$factory->define(EmbedCode::class, function (Faker $faker) {
    return [
        'code' => $faker->paragraph,
    ];
});

$factory->afterCreating(EmbedCode::class, function ($embed_code, $faker) {
    $content_element = factory(ContentElement::class)->states('page')->create([
        'content_id' => $embed_code->id,
        'content_type' => get_class($embed_code),
    ]);
});

