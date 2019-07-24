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
    // 店员列表
    Route::get('stores/clerks', 'StoresController@clerksIndex');
    Route::post('stores/clerks', 'StoresController@addClerk');
    Route::delete('stores/clerks/{clerk}', 'StoresController@delClerk');

    // 商品管理
    Route::get('products', 'ProductsController@index');
    Route::post('products', 'ProductsController@store');
    // 扫码查看商品
    Route::get('products/scan', 'ProductsController@scan');
    Route::get('products/{store_product}', 'ProductsController@show');
    Route::post('products/upload_img', 'ProductsController@uploadImg');
    Route::put('products/{store_product}', 'ProductsController@update');

    Route::post('users/info', 'UsersController@updateInfo');
});

