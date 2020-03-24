<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ContentElement;
use Faker\Generator as Faker;

use App\Page;
use App\Version;
use App\TextBlock;
use App\Quote;
use App\Photo;
use Illuminate\Support\Str;

$factory->define(ContentElement::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid(),
        'page_id' => factory(Page::class)->create()->id,
        'sort_order' => $faker->randomNumber(1),
        'version_id' => factory(Version::class)->create()->id,
        'unlisted' => false,
    ];
});

$factory->state(ContentElement::class, 'unlisted', function ($faker) {
    return [
        'unlisted' => true,
    ];
});

$factory->state(ContentElement::class, 'text-block', function ($faker) {
    $text_block = factory(TextBlock::class)->create();
    return [
        'content_id' => $text_block->id,
        'content_type' => get_class($text_block),
    ];
});

$factory->state(ContentElement::class, 'photo-block', function ($faker) {
    $photo = factory(Photo::class)->states('photo-block')->create();
    $photo_block = $photo->content;
    return [
        'content_id' => $photo_block->id,
        'content_type' => get_class($photo_block),
    ];
});

$factory->state(ContentElement::class, 'quote', function ($faker) {
    $photo = factory(Photo::class)->states('quote')->create();
    $quote = $photo->content;
    return [
        'content_id' => $quote->id,
        'content_type' => get_class($quote),
    ];
});
