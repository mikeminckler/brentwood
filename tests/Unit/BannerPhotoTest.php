<?php

namespace Tests\Unit;

use Tests\TestCase;

use Tests\Unit\PageLinkTestTrait;

use App\Models\BannerPhoto;

class BannerPhotoTest extends TestCase
{
    use PageLinkTestTrait;

    protected function getModel()
    {
        return $this->createContentElement(BannerPhoto::factory())->content;
    }

    protected function getLinkFields()
    {
        return [
            'body',
        ];
    }
}
