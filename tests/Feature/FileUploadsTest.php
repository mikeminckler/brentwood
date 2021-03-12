<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadsTest extends TestCase
{
    /** @test **/
    public function a_file_can_be_uploaded()
    {
        $this->withoutExceptionHandling();
        Storage::fake();
        $file = UploadedFile::fake()->image('logo.png');

        $this->signInAdmin();

        $this->json('POST', route('file-uploads.create'), ['file' => $file])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'logo.png Uploaded'
            ]);

        $file_upload = FileUpload::all()->last();

        $this->assertInstanceOf(FileUpload::class, $file_upload);
        Storage::assertExists('uploads/'.$file->hashName());
    }


    /** @test **/
    public function removing_a_file_upload()
    {
        $file_upload = FileUpload::factory()->create();
        $this->withoutExceptionHandling();
        $this->assertInstanceOf(FileUpload::class, $file_upload);
        Storage::assertExists($file_upload->storage_filename);

        $this->signInAdmin();

        $this->json('POST', route('file-uploads.destroy', ['id' => $file_upload->id]))
            ->assertOK()
            ->assertJsonFragment(['success' => 'File removed']);
        $this->assertEquals(0, FileUpload::where('id', $file_upload->id)->get()->count());
    }

    /** @test **/
    public function a_file_upload_can_only_be_of_a_certain_type()
    {
        Storage::fake();
        $sh = UploadedFile::fake()->create('logo.sh', 100);

        $this->signInAdmin();

        $this->json('POST', route('file-uploads.create'), ['file' => $sh])
            ->assertStatus(422)
            ->assertJsonFragment([
                'errors' => [
                    'file' => [
                        'The file must be a file of type: jpeg, png, pdf.'
                    ],
                ],
            ]);
    }

    /** @test **/
    public function a_file_upload_cannot_be_over_a_certain_size()
    {
        Storage::fake();
        $too_big = UploadedFile::fake()->create('logo.jpg', 20048);

        $this->signInAdmin();

        $this->json('POST', route('file-uploads.create'), ['file' => $too_big])
            ->assertStatus(422)
            ->assertJsonFragment([
                'errors' => [
                    'file' => [
                        'The file may not be greater than 10240 kilobytes.'
                    ],
                ],
            ]);
    }

    /** @test **/
    public function an_uploaded_image_needs_to_pass_validation()
    {
        Storage::fake();
        $bad_image = UploadedFile::fake()->create('logo.pdf', 40124);
        $good_image = UploadedFile::fake()->image('logo.png');

        $this->signInAdmin();

        $this->json('POST', route('file-uploads.create'), ['file' => $bad_image, 'type' => 'image'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'errors' => [
                    'file' => [
                        'The file may not be greater than 30720 kilobytes.',
                        'The file must be a file of type: jpeg, png.',
                    ],
                ],
            ]);

        $this->json('POST', route('file-uploads.create'), ['file' => $good_image, 'type' => 'image'])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'logo.png Uploaded'
            ]);

        $file_upload = FileUpload::all()->last();

        $this->assertInstanceOf(FileUpload::class, $file_upload);
        Storage::assertExists('uploads/'.$good_image->hashName());
    }

    /** @test **/
    public function a_file_needs_valid_data_for_prevalidated_before_upload()
    {
        $this->signInAdmin();

        Storage::fake();
        $file = UploadedFile::fake()->create('logo.gif', 20124);

        $input = [
            'name' => $file->name,
            'size' => $file->sizeToReport,
        ];

        $this->json('POST', route('file-uploads.pre-validate'), $input)
            ->assertStatus(422)
            ->assertJsonFragment([
                'errors' => [
                    'extension' => [
                        'The selected extension is invalid.',
                    ],
                    'size' => [
                        'The size may not be greater than '.(new FileUpload)->max_size.'.',
                    ],
                ],
            ]);
    }

    /** @test **/
    public function a_file_can_be_prevalidated_before_upload()
    {
        $this->signInAdmin();

        Storage::fake();

        $file = UploadedFile::fake()->create('logo.pdf', 512);

        $input = [
            'name' => $file->name,
            'size' => $file->sizeToReport,
        ];

        $this->json('POST', route('file-uploads.pre-validate'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'File valid'
            ]);
    }

    /** @test **/
    public function an_image_needs_prevalidation_for_upload()
    {
        $this->signInAdmin();

        Storage::fake();

        $file = UploadedFile::fake()->create('logo.png', 2048);

        $input = [
            'name' => $file->name,
            'size' => $file->sizeToReport,
            'type' => 'image',
        ];

        $this->json('POST', route('file-uploads.pre-validate'), $input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'File valid'
            ]);
    }

    /** @test **/
    public function a_file_upload_name_has_illegal_characters_removed()
    {
        $this->withoutExceptionHandling();
        Storage::fake();
        $file = UploadedFile::fake()->image('!@#$%^&*()foobar"[];<>\'.png');

        $this->signInAdmin();

        $this->json('POST', route('file-uploads.create'), ['file' => $file])
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => 'foobar.png Uploaded'
            ]);

        $file_upload = FileUpload::all()->last();

        $this->assertInstanceOf(FileUpload::class, $file_upload);
        Storage::assertExists('uploads/'.$file->hashName());
        $this->assertEquals($file_upload->name, 'foobar.png');
    }
}
