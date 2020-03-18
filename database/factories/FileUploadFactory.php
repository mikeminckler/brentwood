<?php
  
use App\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(FileUpload::class, function (Faker $faker) {
    Storage::fake();
    $file = UploadedFile::fake()->create(Str::random(), 100);
    $storage_filename = Storage::putFile('uploads', $file);
    return [
        'storage_filename' => $storage_filename,
        'name' => $faker->firstName,
        'filename' => $faker->firstName,
        'extension' => $faker->fileExtension,
        'mime' => $faker->mimeType,
        'size' => 100000,
    ];
});

$factory->state(FileUpload::class, 'jpg', function (Faker $faker) {
    $filename = Str::random();
    $name = $filename.'.jpg';
    Storage::fake();
    $file = UploadedFile::fake()->image($name);
    $storage_filename = Storage::putFile('photos', $file);
    return [
        'storage_filename' => $storage_filename,
        'name' => $name,
        'filename' => $filename,
        'extension' => 'jpg',
        'mime' => 'image/jpg',
        'size' => $faker->randomNumber,
    ];
});

$factory->state(FileUpload::class, 'png', function (Faker $faker) {
    $filename = Str::random();
    $name = $filename.'.png';
    Storage::fake();
    $file = UploadedFile::fake()->image($name);
    $storage_filename = Storage::putFile('photos', $file);
    return [
        'storage_filename' => $storage_filename,
        'name' => $name,
        'filename' => $filename,
        'extension' => 'png',
        'mime' => 'image/png',
        'size' => $faker->randomNumber,
    ];
});
