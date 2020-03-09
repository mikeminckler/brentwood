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

Route::get('/', 'PagesController@load');
Auth::routes();


Route::group(['middleware' => ['auth']], function () {

    Route::post('/pages', 'PagesController@index')->name('pages.index');
    Route::post('/pages/create', 'PagesController@store')->name('pages.store');

    Route::post('/editing-toggle', 'SessionsController@editingToggle')->name('editing-toggle');

});
