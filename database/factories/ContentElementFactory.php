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

$factory->define(ContentElement::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid(),
        'version_id' => factory(Version::class)->create()->id,
        //'page_id' => factory(Page::class)->create()->id,
        //'sort_order' => $faker->randomNumber(1),
        //'unlisted' => false,
    ];
});

$factory->afterCreating(ContentElement::class, function($content_element, $faker) {
    $page = factory(Page::class)->create();
    //$content_element->version_id = $page->getDraftVersion()->id;
    //$content_element->save();
    //$content_element->refresh();
    $content_element->pages()->attach($page, [
        'sort_order' => $faker->randomNumber(1),
        'unlisted' => false,
    ]);
});

$factory->state(ContentElement::class, 'unlisted', function ($faker) {
    return [
    ];
});

$factory->afterCreatingState(ContentElement::class, 'unlisted', function($content_element, $faker) {
    $page = $content_element->pages->first();
    $content_element->pages()->updateExistingPivot($page, [
        'sort_order' => $faker->randomNumber(1),
        'unlisted' => true,
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
