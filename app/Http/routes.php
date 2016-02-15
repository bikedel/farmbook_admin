<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


 
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});

Route::group(['middleware' => 'web'], function () {

    Route::auth();


    Route::get('/', function () {
        return view('welcome');
    });






    Route::get('/home', 'HomeController@index');

    // show street
    Route::post('/street', 'StreetController@index');
    Route::get('/street/{id}', 'StreetController@rolledit');

    Route::post('/complex', 'ComplexController@index');
    Route::get('/complex/{id}', 'ComplexController@rolledit');


    // show property
    Route::get('/property/{id}', 'PropertyController@edit');
    Route::post('/property/{id}', 'PropertyController@update');



    // set user database dynamically
    Route::get('/userfarmbooks', 'UserController@listFarmbooks');
    Route::post('/setuserfarmbook', 'UserController@setFarmbook');


    // user admin
    Route::get('/users', 'UserController@index');  
    Route::get('/user/{id}', 'UserController@edit');
    Route::post('/user/{id}', 'UserController@store');


    // farmbook admin
    Route::get('/farmbooks', 'FarmbookController@index');  
    Route::get('/farmbook/{id}', 'FarmbookController@edit');
    Route::post('/farmbook/{id}', 'FarmbookController@store');

    //import
    Route::get('/import', 'CsvImportController@index');
    Route::post('/import', 'CsvImportController@store');

    // create database
    Route::post('/createdatabase', 'CsvImportController@createdatabase');



    // rubish
    Route::get('/farmbook', 'FarmbookController@index');    
    Route::get('/settings', 'SettingsController@index');    

});
