<?php
  
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUpload extends Model
{
    use SoftDeletes;

    protected $hidden = ['storage_filename'];

    public $max_size = 1024;
    public $extensions = [
        'jpg',
        'jpeg',
        'png',
        'pdf',
    ];

    public $max_image_size = 20480;
    public $image_extensions = [
        'jpg',
        'jpeg',
        'png',
    ];

    public function saveFile(UploadedFile $file, $directory = null, $public = null)
    {
        if (!$directory) {
            $directory = 'uploads';
        }
        if ($public) {
            $public = 'public';
        }
        $file_upload = new FileUpload;
        $storage_filename = Storage::putFile($directory, $file, $public);
        $file_upload->storage_filename = $storage_filename;

        $file_name = $file->getClientOriginalName();
        // remove any non standard characets from the file name
        $file_upload->name = preg_replace("/[^A-Za-z0-9\.\-_ ]/", '', $file_name);
        $file_upload->filename = pathinfo($file_name, PATHINFO_FILENAME);
        $file_upload->extension = strtolower($file->getClientOriginalExtension());
        $file_upload->mime = $file->getMimeType();
        $file_upload->size = $file->getSize();

        $file_upload->save();
        return $file_upload;
    }

    public function fileable()
    {
        return $this->morphTo();
    }

    // we use this for validation
    public function isImage()
    {
        if (in_array($this->extension, $this->image_extensions)) {
            return true;
        }
        return false;
    }
}
