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

Auth::routes();

Route::get('/pages', 'PagesController@index')->name('pages.index');

Route::group(['middleware' => ['auth']], function () {

    Route::post('/editing-toggle', 'SessionsController@editingToggle')->name('editing-toggle');

    Route::post('/pages/create', 'PagesController@store')->name('pages.store');
    Route::post('/pages/{id}', 'PagesController@store')->name('pages.update')->where('id', '\d+');
    Route::post('/pages/{id}/publish', 'PagesController@publish')->name('pages.publish')->where('id', '\d+');
    Route::post('/pages/{id}/remove', 'PagesController@remove')->name('pages.remove')->where('id', '\d+');
    Route::post('/pages/{id}/restore', 'PagesController@restore')->name('pages.restore')->where('id', '\d+');

    Route::post('/content-elements/create', 'ContentElementsController@store')->name('content-elements.store');
    Route::post('/content-elements/{id}', 'ContentElementsController@store')->name('content-elements.update')->where('id', '\d+');
    Route::post('/content-elements/{id}/remove', 'ContentElementsController@remove')->name('content-elements.remove')->where('id', '\d+');
    Route::post('/content-elements/{id}/restore', 'ContentElementsController@restore')->name('content-elements.restore')->where('id', '\d+');

    Route::post('/photos/{id}/remove', 'PhotosController@remove')->name('photos.remove')->where('id', '\d+');
    Route::post('/photos/{id}/restore', 'PhotosController@restore')->name('photos.restore')->where('id', '\d+');

    Route::post('/file-uploads/create', 'FileUploadsController@store')->name('file-uploads.create');
    Route::post('/file-uploads/pre-validate', 'FileUploadsController@preValidateFile')->name('file-uploads.pre-validate');
    Route::post('/file-uploads/{id}/destroy', 'FileUploadsController@destroy')->name('file-uploads.destroy')->where('id', '\d+');
});

Route::get('{page}', 'PagesController@load')->name('pages.load')->where('page', '.*');
