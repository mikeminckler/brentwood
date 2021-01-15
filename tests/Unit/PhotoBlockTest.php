<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\PhotoBlock;
use App\Models\Photo;
use App\Models\ContentElement;
use App\Models\Page;
use App\Models\FileUpload;

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
        $photo = Photo::factory()->for(FileUpload::factory()->jpg())->for(PhotoBlock::factory(), 'content')->create();
        $photo_block = $photo->content;
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo_block->photos->first());
        $this->assertTrue($photo_block->photos->contains('id', $photo->id));
    }

    /** @test **/
    public function a_photo_block_belongs_to_a_content_element()
    {
        $page = Page::factory()->create();
        $content_element = ContentElement::factory()->for(PhotoBlock::factory()->has(Photo::factory()->for(FileUpload::factory()->jpg())), 'content')->create([
            'version_id' => $page->draft_version_id,
        ]);
        $photo_block = $content_element->content;
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(ContentElement::class, $photo_block->contentElement);
    }

    /** @test **/
    public function a_photo_block_can_be_duplicated()
    {
        $page = Page::factory()->create();
        $content_element = ContentElement::factory()->for(PhotoBlock::factory()->has(Photo::factory()->for(FileUpload::factory()->jpg())), 'content')->create([
            'version_id' => $page->draft_version_id,
        ]);
        $this->assertEquals(1, $content_element->content->photos->count());
        $photo_block = $content_element->content;

        $this->assertInstanceOf(PhotoBlock::class, $photo_block);

        $input = $photo_block->toArray();
        $new_photo_block = (new PhotoBlock)->saveContent($input, null, true);

        $this->assertInstanceOf(PhotoBlock::class, $new_photo_block);
        $this->assertEquals(1, $new_photo_block->photos->count());

        $photo_block->refresh();
        $this->assertEquals(1, $photo_block->photos->count());
    }
}
