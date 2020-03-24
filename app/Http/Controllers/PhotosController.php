<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Photo;
use App\Http\Controllers\SoftDeletesControllerTrait;

class PhotosController extends Controller
{
    use SoftDeletesControllerTrait;

    protected function getModel()
    {
        return new Photo;
    }
}
