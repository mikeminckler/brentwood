<?php

namespace Database\Factories;

use App\Models\FileUpload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileUploadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FileUpload::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create(Str::random(), 100);
        $storage_filename = Storage::putFile('uploads', $file);
        return [
            'storage_filename' => $storage_filename,
            'name' => $this->faker->firstName,
            'filename' => $this->faker->firstName,
            'extension' => $this->faker->fileExtension,
            'mime' => $this->faker->mimeType,
            'size' => 100000,
        ];
    }

    public function jpg()
    {
        $filename = Str::random();
        $name = $filename.'.jpg';
        Storage::fake();
        $file = UploadedFile::fake()->image($name);
        $storage_filename = Storage::putFile('photos', $file);
        return $this->state([
            'storage_filename' => $storage_filename,
            'name' => $name,
            'filename' => $filename,
            'extension' => 'jpg',
            'mime' => 'image/jpg',
            'size' => $this->faker->randomNumber,
        ]);
    }

    public function png()
    {
        $filename = Str::random();
        $name = $filename.'.png';
        Storage::fake();
        $file = UploadedFile::fake()->image($name);
        $storage_filename = Storage::putFile('photos', $file);
        return $this->state([
            'storage_filename' => $storage_filename,
            'name' => $name,
            'filename' => $filename,
            'extension' => 'png',
            'mime' => 'image/png',
            'size' => $this->faker->randomNumber,
        ]);
    }
}
