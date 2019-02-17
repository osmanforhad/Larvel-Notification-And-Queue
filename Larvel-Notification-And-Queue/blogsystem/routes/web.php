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

Route::get('/','HomeController@index')->name('home');

Route::post('subscriver','SubscriverController@store')->name('subscriver.store');

Auth::routes();


//For Admin Using Middleware Authentication and Prefix Namespace
Route::group(['as'=>'admin.','prefix'=>'admin','namespace'=>'Admin','middleware'=>['auth','admin']],function(){
    Route::get('dashboard','DashboardController@index')->name('dashboard');
    Route::resource('tag','TagController');
    Route::resource('category','CategoryController');
    Route::resource('post','PostController');

    Route::get('pending/post','PostController@pending')->name('post.pending');
    Route::put('/post/{id}/approve','PostController@approval')->name('post.approve');

    Route::get('/subscriber/','SubscriverController@index')->name('subscriber.index');
    Route::delete('/subscriber/{subscriber}','SubscriverController@destroy')->name('subscriber.destroy');

});

//For Author Using Middleware Authentication And Prefix Namespace
Route::group(['as'=>'author.','prefix'=>'author','namespace'=>'Author','middleware'=>['auth','author']],function(){
Route::get('dashboard','DashboardController@index')->name('dashboard');
    Route::resource('post','PostController');
});
