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

Route::get('wx_auth', 'AuthController@wxAuth');   // 微信授权登录

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('stores', 'StoresController@store');  // 添加店铺
    Route::post('stores/upload_img', 'StoresController@uploadImg');  // 添加店铺图片
    Route::get('stores/owner', 'StoresController@getOwner');  //获取店长信息
    Route::post('stores/clerks', 'ClerksController@store');  // 成为店员
    Route::post('users/info', 'UsersController@updateInfo'); // 更新用户数据

    Route::group(['middleware' => 'has_store'], function () {
        Route::delete('stores', 'StoresController@destroy');  // 注销店铺

        Route::delete('clerks/quit', 'ClerksController@quit'); // 退出店铺
        Route::get('stores/clerks', 'ClerksController@index'); // 店员列表
        Route::delete('stores/clerks/{clerk}', 'ClerksController@destroy'); // 移除店员
        Route::post('clerks/form_id', 'ClerksController@storeFormId');  // 保存店长的form_id

        Route::post('permissions', 'PermissionsController@store'); // 配置店员权限

        Route::get('products', 'ProductsController@index'); // 商品列表
        Route::get('products/{store_product}', 'ProductsController@show');  //商品详情
        Route::post('products/upload_img', 'ProductsController@uploadImg'); // 上传商品图片
        Route::post('products', 'ProductsController@store'); // 添加商品
        Route::put('products/{store_product}', 'ProductsController@update'); // 更新商品
        Route::delete('products/{store_product}', 'ProductsController@destroy'); // 删除商品

        Route::get('products/scan', 'ProductsController@scan');    // 扫码查看商品
    });
});

