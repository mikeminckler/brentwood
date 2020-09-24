<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\PhotoBlock;
use App\Models\Photo;
use App\Models\ContentElement;
use App\Models\Page;

use Tests\Unit\PageLinkTestTrait;

class PhotoBlockTest extends TestCase
{
    use PageLinkTestTrait;

    protected function getModel()
    {
        return $this->createContentElement(PhotoBlock::factory())->content;
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
        $photo = Photo::factory()->for(PhotoBlock::factory(), 'content')->create();
        $photo_block = $photo->content;
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo_block->photos->first());
        $this->assertTrue($photo_block->photos->contains('id', $photo->id));
    }

    /** @test **/
    public function a_photo_block_belongs_to_a_content_element()
    {
        $page = Page::factory()->create();
        $content_element = ContentElement::factory()->for(PhotoBlock::factory()->has(Photo::factory()), 'content')->create([
            'version_id' => $page->draft_version_id,
        ]);
        $photo_block = $content_element->content;
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(ContentElement::class, $photo_block->contentElement);
    }
}
