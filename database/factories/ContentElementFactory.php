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
use App\YoutubeVideo;
use App\EmbedCode;
use App\FeaturedPhoto;

$factory->define(ContentElement::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid(),
        'version_id' => factory(Version::class)->create()->id,
    ];
});

$factory->afterCreating(ContentElement::class, function($content_element, $faker) {
    $page = factory(Page::class)->create();
    $content_element->pages()->attach($page, [
        'sort_order' => $faker->randomNumber(1),
        'unlisted' => false,
        'expandable' => false,
    ]);
});

$factory->state(ContentElement::class, 'unlisted', function ($faker) {
    return [
    ];
});

$factory->afterCreatingState(ContentElement::class, 'unlisted', function($content_element, $faker) {
    $page = $content_element->pages->first();
    $content_element->pages()->updateExistingPivot($page, [
        'unlisted' => true,
    ]);
});

$factory->state(ContentElement::class, 'expandable', function ($faker) {
    return [
    ];
});

$factory->afterCreatingState(ContentElement::class, 'expandable', function($content_element, $faker) {
    $page = $content_element->pages->first();
    $content_element->pages()->updateExistingPivot($page, [
        'expandable' => true,
    ]);
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

$factory->state(ContentElement::class, 'youtube-video', function ($faker) {
    $youtube_video = factory(YoutubeVideo::class)->create();
    return [
        'content_id' => $youtube_video->id,
        'content_type' => get_class($youtube_video),
    ];
});

$factory->state(ContentElement::class, 'embed-code', function ($faker) {
    $embed_code = factory(EmbedCode::class)->create();
    return [
        'content_id' => $embed_code->id,
        'content_type' => get_class($embed_code),
    ];
});

$factory->state(ContentElement::class, 'featured-photo', function ($faker) {
    $featured_photo = factory(FeaturedPhoto::class)->create();
    return [
        'content_id' => $featured_photo->id,
        'content_type' => get_class($featured_photo),
    ];
});
