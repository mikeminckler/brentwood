<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\PhotoBlock;
use App\Photo;
use App\ContentElement;

use Tests\Unit\PageLinkTestTrait;

class PhotoBlockTest extends TestCase
{

    use PageLinkTestTrait;

    protected function getModel()
    {
        return factory(PhotoBlock::class)->create();
    }

    protected function getLinkFields()
    {
        return [
            'body',
        ];
    }

    /** @test **/
    public function a_photo_block_has_many_photos()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $photo_block = $photo->content;
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo_block->photos->first());
        $this->assertTrue($photo_block->photos->contains('id', $photo->id));
    }

    /** @test **/
    public function a_photo_block_belongs_to_a_content_element()
    {
        $photo = factory(Photo::class)->states('photo-block')->create();
        $photo_block = $photo->content;
        $this->assertInstanceOf(ContentElement::class, $photo_block->contentElement);
    }

}
