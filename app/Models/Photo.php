<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\PhotoValidation;

use Intervention\Image\Facades\Image;

use App\Models\FileUpload;
use App\Models\PhotoBlock;
use App\Utilities\PageLink;

class Photo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $with = ['fileUpload'];
    protected $appends = ['small', 'medium', 'large'];

    public function savePhoto(array $input, $id = null, $content = null)
    {
        if (!$input) {
            return null;
        }

        $validation = Validator::make($input, (new PhotoValidation)->rules());
        if ($validation->fails()) {
            // TODO we should throw a 422 here probably
            if (!env('production')) {
                throw ValidationException::withMessages($validation->errors()->all());
            }
            return null;
        }

        if ($id >= 1) {
            $photo = Photo::findOrFail($id);
            if (!$content) {
                $content = $photo->content;
            }
        } else {

            // we need to check for the same filename
            // in the content element as we can save
            // the content element while the image is still processing

            if ($content->photos()->count()) {
                $existing_photo = $content->photos()->get()->filter(function ($photo) use ($input) {
                    return $photo->fileUpload->id === Arr::get($input, 'file_upload.id');
                })->first();

                if ($existing_photo) {
                    $photo = $existing_photo;
                } else {
                    $photo = new Photo;
                }
            } else {
                $photo = new Photo;
            }
        }

        $photo->content_id = $content->id;
        $photo->content_type = get_class($content);

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
        $photo->fill = Arr::get($input, 'fill');
        $photo->stat_number = Arr::get($input, 'stat_number');
        $photo->stat_name = Arr::get($input, 'stat_name');
        $photo->link = Arr::get($input, 'link');

        $photo->save();

        if ($photo->fileUpload) {
            if ($photo->fileUpload->id !== $file_upload->id) {
                $photo->fileUpload()->delete();
                $photo->removeSmall();
                $photo->removeMedium();
                $photo->removeLarge();
            }
        }

        $photo->fileUpload()->save($file_upload);

        cache()->tags([cache_name($photo)])->flush();

        $photo->refresh();
        return $photo;
    }

    public function content()
    {
        return $this->morphTo();
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
                    return $this->createImage(400, 'small');
                }
            } else {
                return $this->createImage(400, 'small');
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
                    return $this->createImage(900, 'medium');
                }
            } else {
                return $this->createImage(900, 'medium');
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
                    return $this->createImage(1152, 'large');
                }
            } else {
                return $this->createImage(1152, 'large');
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

    public function getLinkAttribute($value)
    {
        return PageLink::convertLink($value);
    }
}
