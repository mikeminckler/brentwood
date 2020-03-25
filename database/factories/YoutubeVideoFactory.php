<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\YoutubeVideo;
use Faker\Generator as Faker;
use App\ContentElement;

$factory->define(YoutubeVideo::class, function (Faker $faker) {
    return [
        'video_id' => '1tW0Zj3YoJ4',
    ];
});

$factory->afterCreating(YoutubeVideo::class, function ($youtube_video, $faker) {
    $content_element = factory(ContentElement::class)->create([
        'content_id' => $youtube_video->id,
        'content_type' => get_class($youtube_video),
    ]);
});

