<?php
  
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;

use App\FileUpload;
use Illuminate\Support\Str;
use App\Http\Requests\FileUploadValidation;

class FileUploadsController extends Controller
{
    public function store()
    {
        $class_name = 'App\\Http\\Requests\\'.(request('type') ? Str::studly(request('type')): 'FileUpload').'Validation';
        $rules = (new $class_name)->rules();
        $validator = request()->validate($rules);

        $file_upload = (new FileUpload)->saveFile(request('file'), request('folder'), request('public'));
        return response()->json([
            'success' => $file_upload->name.' Uploaded',
            'file_upload' => $file_upload,
        ]);
    }

    public function destroy($id)
    {
        $file_upload = FileUpload::findOrFail($id);

        if (auth()->user()->can('delete', $file_upload)) {
            $file_upload->delete();

            return response()->json([
                'success' => 'File removed'
            ]);
        } else {
            return response()->json([
                'error' => 'You do not have permission to delete that file'
            ], 403);
        }
    }

    public function preValidateFile()
    {
        $extension = strtolower(pathinfo(request('name'), PATHINFO_EXTENSION));
        $size = request('size') / 1024;
        $input = [
            'extension' => $extension,
            'size' => $size,
        ];

        $valid_extensions = (new FileUpload)->extensions;
        $max_size = (new FileUpload)->max_size;

        if (request('type')) {
            if (request('type') == 'image') {
                $valid_extensions = (new FileUpload)->image_extensions;
                $max_size = (new FileUpload)->max_image_size;
            }
        }

        $validator = Validator::make($input, [
            'extension' => 'required|in:'.implode(',', $valid_extensions),
            'size' => 'required|numeric|max:'.$max_size,
        ])->validate();

        return response()->json([
            'success' => 'File valid',
        ]);
    }
}
