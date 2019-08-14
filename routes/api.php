<?php

use Illuminate\Support\Facades\Route;

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

Route::get('wx_auth', 'AuthController@wxAuth');

Route::group(['middleware' => 'auth:api'], function () {
    // 店铺管理
    Route::post('stores', 'StoresController@store');
    Route::post('stores/upload_img', 'StoresController@uploadImg');
    Route::delete('stores', 'StoresController@destroy')
        ->middleware('has_store');

    // 店员操作
    Route::get('stores/clerks', 'ClerksController@index')
        ->middleware('has_store');

    Route::post('stores/clerks', 'ClerksController@store');

    Route::delete('clerks/quit', 'ClerksController@quit')
        ->middleware('has_store');

    Route::delete('stores/clerks/{clerk}', 'ClerksController@destroy')
        ->middleware('has_store');

    // 商品管理
    Route::get('products', 'ProductsController@index')
        ->middleware('has_store');
    Route::post('products', 'ProductsController@store')
        ->middleware('has_store');

    // 扫码查看商品
    Route::get('products/scan', 'ProductsController@scan');

    Route::get('products/{store_product}', 'ProductsController@show')
        ->middleware('has_store');
    Route::post('products/upload_img', 'ProductsController@uploadImg');
    Route::put('products/{store_product}', 'ProductsController@update')
        ->middleware('has_store');
    Route::delete('products/{store_product}', 'ProductsController@destroy')
        ->middleware('has_store');

    Route::post('users/info', 'UsersController@updateInfo');
});

