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

Route::get('/', 'MainController@home')->name('home');
Route::get('/ref/{year}/{suffix}', 'MainController@reference')->name('get_reference');
Route::get('/ref/', 'MainController@getSuffix')->name('get_suffix');
