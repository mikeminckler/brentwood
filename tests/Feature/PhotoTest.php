<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Photo;
use Tests\Feature\SoftDeletesTestTrait;

class PhotoTest extends TestCase
{
    use SoftDeletesTestTrait;

    protected function getModel()
    {
        return factory(Photo::class)->states('photo-block')->create();
    }

}
