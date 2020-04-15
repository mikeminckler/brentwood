<?php

namespace Tests\Unit;

use Tests\TestCase;

use Tests\Unit\PageLinkTestTrait;

use App\BannerPhoto;

class BannerPhotoTest extends TestCase
{
    use PageLinkTestTrait;

    protected function getModel()
    {
        return factory(BannerPhoto::class)->create();
    }

    protected function getLinkFields()
    {
        return [
            'body',
        ];
    }
}
