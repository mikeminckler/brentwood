<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Photo;
use App\Traits\SoftDeletesControllerTrait;
use App\Http\Requests\PhotoValidation;

class PhotosController extends Controller
{
    use SoftDeletesControllerTrait;

    protected function getModel()
    {
        return new Photo;
    }

    public function store(PhotoValidation $request, $id)
    {
        $photo = (new Photo)->savePhoto(requestInput(), $id);

        return response()->json([
            'success' => 'Photo Saved',
            'photo' => $photo,
        ]);
    }
}
