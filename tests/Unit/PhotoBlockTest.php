<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\PhotoBlock;
use App\Photo;

class PhotoBlockTest extends TestCase
{

    /** @test **/
    public function a_photo_block_has_many_photos()
    {
        $photo = factory(Photo::class)->create();
        $photo_block = $photo->photoBlock;
        $this->assertInstanceOf(PhotoBlock::class, $photo_block);
        $this->assertInstanceOf(Photo::class, $photo_block->photos->first());
        $this->assertTrue($photo_block->photos->contains('id', $photo->id));
    }
}
