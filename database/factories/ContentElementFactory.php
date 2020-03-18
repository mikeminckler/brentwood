<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ContentElement;
use Faker\Generator as Faker;

use App\Page;
use App\Version;
use App\TextBlock;

$factory->define(ContentElement::class, function (Faker $faker) {
    return [
        'page_id' => factory(Page::class)->create()->id,
        'sort_order' => $faker->randomNumber(1),
        'version_id' => factory(Version::class)->create()->id,
    ];
});

$factory->state(ContentElement::class, 'text-block', function ($faker) {
    $text_block = factory(TextBlock::class)->create();
    return [
        'content_id' => $text_block->id,
        'content_type' => get_class($text_block),
    ];
});
