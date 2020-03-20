<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\PhotoValidation;

use Intervention\Image\Facades\Image;
use App\FileUpload;
use App\PhotoBlock;

class Photo extends Model
{
    use SoftDeletes;

    protected $with = ['fileUpload'];
    protected $appends = ['small', 'medium', 'large'];


    public function savePhoto($id = null, $input)
    {
        if (!$input) {
            return null;
        }

        $validation = Validator::make($input, (new PhotoValidation)->rules());
        if ($validation->fails()) {
            // TODO we should throw a 422 here probably
            return null;
        }

        $update = false;
        if ($id >= 1) {
            $photo = Photo::findOrFail($id);
            $update = true;
        } else {
            $photo = new Photo;
        }

        $photo_block = PhotoBlock::findOrFail(Arr::get($input, 'photo_block_id'));
        $photo->photo_block_id = $photo_block->id;

        $file_upload = FileUpload::findOrFail(Arr::get($input, 'file_upload.id'));

        if (Arr::get($input, 'name')) {
            $name = Arr::get($input, 'name');
        } else {
            $name = $file_upload->name;
        }

        $photo->name = $name;
        $photo->description = Arr::get($input, 'description');
        $photo->alt = Arr::get($input, 'alt');

        $photo->sort_order = Arr::get($input, 'sort_order');
        $photo->span = Arr::get($input, 'span');
        $photo->offsetX = Arr::get($input, 'offsetX');
        $photo->offsetY = Arr::get($input, 'offsetY');

        $photo->save();

        $photo->fileUpload()->save($file_upload);

        return $photo;
    }

    public function photoBlock() 
    {
        return $this->belongsTo(PhotoBlock::class);   
    }

    public function fileUpload()
    {
        return $this->morphOne(FileUpload::class, 'fileable');
    }

    public function createImage($size, $name)
    {
        if (!Storage::exists(optional($this->fileUpload)->storage_filename)) {
            // TODO we should check for orphans here
            return '/public/images/default.png';
        }
        $file = Storage::get($this->fileUpload->storage_filename);
        $image = Image::make($file)
            ->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

        $webp = Image::make($file)
            ->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('webp');

        $file_name = '/photos/'.$this->id.'/'.$this->fileUpload->filename.'-'.$name.'.'.$this->fileUpload->extension;
        Storage::disk('public')->put($file_name, $image->stream());
        Storage::disk('public')->put($file_name.'.webp', $webp->stream());
        $this->{$name} = $file_name;
        $this->save();
        //cache()->tags([cache_name($this)])->flush();
        return $file_name;
    }

    public function getSmallAttribute($value)
    {
        return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-small', function () use ($value) {
            if ($value) {
                if (Storage::disk('public')->exists($value)) {
                    return $value;
                } else {
                    return $this->createImage(200, 'small');
                }
            } else {
                return $this->createImage(200, 'small');
            }
        });
    }

    public function removeSmall()
    {
        if ($this->small) {
            if (Storage::disk('public')->exists($this->small)) {
                Storage::disk('public')->delete($this->small);
            }
        }
    }

    public function getMediumAttribute($value)
    {
        return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-medium', function () use ($value) {
            if ($value) {
                if (Storage::disk('public')->exists($value)) {
                    return $value;
                } else {
                    return $this->createImage(600, 'medium');
                }
            } else {
                return $this->createImage(600, 'medium');
            }
        });
    }

    public function removeMedium()
    {
        if ($this->medium) {
            if (Storage::disk('public')->exists($this->medium)) {
                Storage::disk('public')->delete($this->medium);
            }
        }
    }

    public function getLargeAttribute($value)
    {
        return cache()->tags([cache_name($this)])->rememberForever(cache_name($this).'-large', function () use ($value) {
            if ($value) {
                if (Storage::disk('public')->exists($value)) {
                    return $value;
                } else {
                    return $this->createImage(1200, 'large');
                }
            } else {
                return $this->createImage(1200, 'large');
            }
        });
    }

    public function removeLarge()
    {
        if ($this->large) {
            if (Storage::disk('public')->exists($this->large)) {
                Storage::disk('public')->delete($this->large);
            }
        }
    }
}
