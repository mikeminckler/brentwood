<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Version;
use Faker\Generator as Faker;

use App\Page;
use App\Blog;

$factory->define(Version::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName,
        //'page_id' => factory(Page::class)->create()->id,
    ];
});

$factory->state(Version::class, 'page', function ($faker) {
    $page = factory(Page::class)->create();
    return [
        'versionable_id' => $page->id,
        'versionable_type' => get_class($page),
    ];
});

$factory->state(Version::class, 'blog', function ($faker) {
    $blog = factory(Blog::class)->create();
    return [
        'versionable_id' => $blog->id,
        'versionable_type' => get_class($blog),
    ];
});

$factory->state(Version::class, 'published', function ($faker) {
    return [
        'published_at' => now(),
    ];
});
