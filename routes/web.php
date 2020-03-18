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

    Route::post('/pages/create', 'PagesController@store')->name('pages.store');
    Route::post('/pages/{id}', 'PagesController@store')->name('pages.update')->where('id', '\d+');
    Route::post('/editing-toggle', 'SessionsController@editingToggle')->name('editing-toggle');

    Route::post('/content-elements/create', 'ContentElementsController@store')->name('content-elements.store');
});

Route::get('{page}', 'PagesController@load')->name('pages.load')->where('page', '.*');
