<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use Tests\Unit\PageLinkTestTrait;

use App\FeaturedPhoto;

class FeaturedPhotoTest extends TestCase
{
    use PageLinkTestTrait;

    protected function getModel()
    {
        return factory(FeaturedPhoto::class)->create();
    }

    protected function getLinkFields()
    {
        return [
            'body',
        ];
    }
}
