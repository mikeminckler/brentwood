<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Photo;
use App\Models\PhotoBlock;
use App\Models\FileUpload;

class FileUploadTest extends TestCase
{

    /** @test **/
    public function a_file_upload_can_have_many_photos()
    {
        $photo1 = Photo::factory()->for(PhotoBlock::factory(), 'content')->create();
        $photo_block = $photo1->content;
        $file_upload = $photo1->fileUpload;
        $this->assertInstanceOf(FileUpload::class, $file_upload);

        $photo2 = Photo::factory()->create([
            'content_id' => $photo_block->id,
            'content_type' => get_class($photo_block),
            'file_upload_id' => $file_upload->id,
        ]);

        $this->assertEquals(2, $file_upload->photos()->count());
        $this->assertEquals($photo1->fileUpload->id, $photo2->fileUpload->id);
    }
}
