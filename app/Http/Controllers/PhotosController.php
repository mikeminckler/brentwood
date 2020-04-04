<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Photo;
use App\Http\Controllers\SoftDeletesControllerTrait;
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
        $photo = (new Photo)->savePhoto($id, requestInput());

        return response()->json([
            'success' => 'Photo Saved',
            'photo' => $photo,
        ]);
    }
}