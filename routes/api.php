<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::resource('articles','ArticlesController')->only([
    'index', 'show'
]);

Route::get('/articles/{id}/tree',"ArticlesController@tree")->name('articles.tree');

Route::resource('articles','ArticlesController')->except([
    'index', 'show'
]);// TODO: need auth middleware

Route::post('articles_root','ArticlesController@store_root')->name('articles.store_root');

Route::post('upload_image','ArticlesController@uploadImage')->name('articles.upload_image');

Route::post('/login','Auth\LoginController@authenticate')->name('login.api');