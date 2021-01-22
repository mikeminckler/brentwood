<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PageAccessesController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\ContentElementsController;
use App\Http\Controllers\PhotosController;
use App\Http\Controllers\FileUploadsController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\InquiriesController;

//Auth::routes();

Route::get('login', [LoginController::class, 'redirectToProvider'])->name('login');
Route::get('login/google/authorized', [LoginController::class, 'handleProviderCallback']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/pages', [PagesController::class, 'index'])->name('pages.index');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/load', [UsersController::class, 'load'])->name('users.load');
    Route::post('/users/{id}', [UsersController::class, 'store'])->name('users.update')->where('id', '\d+');
    Route::post('/users/search', [UsersController::class, 'search'])->name('users.search');

    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::post('/roles/create', [RolesController::class, 'store'])->name('roles.store');
    Route::post('/roles/{id}', [RolesController::class, 'store'])->name('roles.update')->where('id', '\d+');
    Route::post('/roles/search', [RolesController::class, 'search'])->name('roles.search');
    //Route::post('/roles/{id}/remove-user', [RolesController::class, 'removeUser'])->name('roles.remove-user')->where('id', '\d+');

    Route::get('/page-accesses', [PageAccessesController::class, 'index'])->name('page-accesses.index');
    Route::get('/page-accesses/page/{id}', [PageAccessesController::class, 'page'])->name('page-accesses.page')->where('id', '\d+');
    Route::post('/page-accesses/create', [PageAccessesController::class, 'store'])->name('page-accesses.store');
    Route::post('/page-accesses/{id}/destroy', [PageAccessesController::class, 'destroy'])->name('page-accesses.destroy')->where('id', '\d+');

    Route::post('/editing-toggle', [SessionsController::class, 'editingToggle'])->name('editing-toggle');

    Route::post('/pages/create', [PagesController::class, 'store'])->name('pages.store');
    Route::post('/pages/{id}', [PagesController::class, 'store'])->name('pages.update')->where('id', '\d+');
    Route::post('/pages/{id}/publish', [PagesController::class, 'publish'])->name('pages.publish')->where('id', '\d+');
    Route::post('/pages/{id}/remove', [PagesController::class, 'remove'])->name('pages.remove')->where('id', '\d+');
    Route::post('/pages/{id}/restore', [PagesController::class, 'restore'])->name('pages.restore')->where('id', '\d+');
    Route::post('/pages/{id}/sort', [PagesController::class, 'sortPage'])->name('pages.sort')->where('id', '\d+');
    Route::post('/pages/{id}/unlist', [PagesController::class, 'unlist'])->name('pages.unlist')->where('id', '\d+');
    Route::post('/pages/{id}/reveal', [PagesController::class, 'reveal'])->name('pages.reveal')->where('id', '\d+');

    Route::post('/content-elements/create', [ContentElementsController::class, 'store'])->name('content-elements.store');
    Route::post('/content-elements/{id}', [ContentElementsController::class, 'store'])->name('content-elements.update')->where('id', '\d+');
    Route::post('/content-elements/{id}/load', [ContentElementsController::class, 'load'])->name('content-elements.load')->where('id', '\d+');
    Route::post('/content-elements/{id}/remove', [ContentElementsController::class, 'remove'])->name('content-elements.remove')->where('id', '\d+');
    Route::post('/content-elements/{id}/restore', [ContentElementsController::class, 'restore'])->name('content-elements.restore')->where('id', '\d+');
    Route::post('/content-elements/{id}/publish', [ContentElementsController::class, 'publish'])->name('content-elements.publish')->where('id', '\d+');

    Route::post('/photos/{id}', [PhotosController::class, 'store'])->name('photos.update')->where('id', '\d+');
    Route::post('/photos/{id}/remove', [PhotosController::class, 'remove'])->name('photos.remove')->where('id', '\d+');
    Route::post('/photos/{id}/restore', [PhotosController::class, 'restore'])->name('photos.restore')->where('id', '\d+');

    Route::post('/file-uploads/create', [FileUploadsController::class, 'store'])->name('file-uploads.create');
    Route::post('/file-uploads/pre-validate', [FileUploadsController::class, 'preValidateFile'])->name('file-uploads.pre-validate');
    Route::post('/file-uploads/{id}/destroy', [FileUploadsController::class, 'destroy'])->name('file-uploads.destroy')->where('id', '\d+');

    Route::get('/blogs', [BlogsController::class, 'index'])->name('blogs.index');
    Route::get('/blogs/load', [BlogsController::class, 'load'])->name('blogs.load');
    Route::post('/blogs/create', [BlogsController::class, 'store'])->name('blogs.store');
    Route::post('/blogs/{id}', [BlogsController::class, 'store'])->name('blogs.update')->where('id', '\d+');
    Route::post('/blogs/{id}/publish', [BlogsController::class, 'publish'])->name('blogs.publish')->where('id', '\d+');
    Route::post('/blogs/{id}/remove', [BlogsController::class, 'remove'])->name('blogs.remove')->where('id', '\d+');
    Route::post('/blogs/{id}/restore', [BlogsController::class, 'restore'])->name('blogs.restore')->where('id', '\d+');
    Route::post('/blogs/{id}/unlist', [BlogsController::class, 'unlist'])->name('blogs.unlist')->where('id', '\d+');
    Route::post('/blogs/{id}/reveal', [BlogsController::class, 'reveal'])->name('blogs.reveal')->where('id', '\d+');

    Route::post('/tags/search', [TagsController::class, 'search'])->name('tags.search');
    Route::post('/tags/create', [TagsController::class, 'store'])->name('tags.store');

    Route::get('/inquiries', [InquiriesController::class, 'index'])->name('inquiries.index');
});

Route::post('/blogs', [BlogsController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{page}', [BlogsController::class, 'load'])->name('blogs.load')->where('page', '.*');

Route::get('/inquiry', [InquiriesController::class, 'load'])->name('inquiries.create');
Route::post('/inquiry', [InquiriesController::class, 'store'])->name('inquiries.store');
Route::get('/inquiry/tags', [InquiriesController::class, 'tags'])->name('inquiries.tags');
Route::get('/inquiry/{id}', [InquiriesController::class, 'view'])->name('inquiries.view')->where('id', '\d+');
Route::post('/inquiry/{id}', [InquiriesController::class, 'store'])->name('inquiries.update')->where('id', '\d+');

Route::get('{page}', [PagesController::class, 'load'])->name('pages.load')->where('page', '.*');
