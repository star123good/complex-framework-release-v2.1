<?php

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      router
 *
 *      define Router class object
 *      routing...
 *
**********************************************************************************************/


use Library\Route as Route;


Route::get('', 'Home@index');
Route::get('/login', 'User@login');
Route::get('/signup', 'User@signup');
Route::get('/logout', 'User@logout');
Route::get('/forgot-password', 'User@forgotPassword');
Route::post('/login', 'User@signin');
Route::post('/signup', 'User@register');
Route::get('/test/{id}/{type}', 'Home@test')->middleware('Auth');
Route::rest('/post', 'Post')->middleware('Auth');

Route::get('/admin', 'Admin\Dashboard@index');
