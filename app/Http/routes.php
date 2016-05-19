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

    //Route::auth();
    // Authentication Routes...
    $this->get('login', 'Auth\AuthController@showLoginForm');
    $this->post('login', 'Auth\AuthController@login');
    $this->get('logout', 'Auth\AuthController@logout');

    // Registration Routes...
    $this->get('myregister', 'Auth\AuthController@showRegistrationForm');
    $this->post('register', 'Auth\AuthController@register');

    // Password Reset Routes...
    $this->get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
    $this->post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
    $this->post('password/reset', 'Auth\PasswordController@reset');

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/home', 'HomeController@index');

    // show street
    Route::post('/street', 'StreetController@index');
    Route::get('/street/{id}', 'StreetController@rolledit');
    Route::get('/street/{id}?page={item}', 'StreetController@rolledit');
    //Route::post('/addStreet', 'StreetController@add');

    // complex
    Route::post('/complex', 'ComplexController@index');
    Route::get('/complex/{id}', 'ComplexController@rolledit');
    Route::get('/complex/{id}?page={item}', 'ComplexController@rolledit');

    // erf
    Route::post('/erf', 'ErfController@index');
    Route::get('/erf/{id}', 'ErfController@rolledit');
    Route::get('/erf/{id}?page={item}', 'ErfController@rolledit');

    // owners
    Route::post('/owner', 'OwnerController@index');
    Route::get('/owner/{id}', 'OwnerController@rolledit');
    Route::get('/owner/{id}?page={item}', 'OwnerController@rolledit');

    // show property
    Route::get('/property/{id}', 'PropertyController@edit');
    Route::post('/property/{id}', 'PropertyController@update');

    // print by street
    Route::get('/print/{id}', 'ReportController@printreport');
    // prine by complex - only new ones with 'wrote' in notes
    Route::get('/printNew/{id}', 'ReportController@printreportNew');

    // print by complex
    Route::get('/printcomplex/{id}', 'ReportController@printbycomplexreport');
    // prine by complex - only new ones with 'wrote' in notes
    Route::get('/printcomplexNew/{id}', 'ReportController@printbycomplexreportNew');

    // print all new notes
    Route::get('/printupdates', 'ReportController@printupdates');

    Route::get('/prog', 'ReportController@testreport');

    // print follow ups
    Route::get('/printfollowups', 'ReportController@printfollowups');

    // set user database dynamically
    Route::get('/userfarmbooks', 'UserController@listFarmbooks');
    Route::post('/setuserfarmbook', 'UserController@setFarmbook');

    // user admin
    Route::get('/users', 'UserController@index');
    Route::get('/user/{id}', 'UserController@edit');
    Route::post('/user/{id}', 'UserController@store');
    Route::get('/adduser', 'UserController@adduser');
    Route::post('/adduser', 'UserController@storeadduser');
    Route::post('/deleteuser/{id}', 'UserController@delete');

    // farmbook admin
    Route::get('/farmbooks', 'FarmbookController@index');
    Route::get('/farmbook/{id}', 'FarmbookController@edit');
    Route::post('/farmbook/{id}', 'FarmbookController@store');
    Route::post('/farmbookdelete/{id}', 'FarmbookController@delete');

    // global - farmbooks and users
    Route::get('glob', 'DashboardController@glob');

    //global search
    Route::get('/globalsearch', 'DashboardController@Search');
    Route::post('/globalsearch', 'DashboardController@globSearch');

    //import
    Route::get('/import', 'CsvImportController@index');
    Route::post('/import', 'CsvImportController@store');

    //update
    Route::post('/updateFH', 'UpdateController@updateFH');
    Route::post('/updateST', 'UpdateController@updateST');

    // create database
    Route::post('/createdatabase', 'CsvImportController@createdatabase');

    // delete database
    Route::post('/deletedatabase', 'CsvImportController@deletedatabase');

    //logging
    Route::get('/logs', 'LogsController@index');
    Route::get('/deletelogs', 'LogsController@destroy');

    //update logs
    Route::get('/listlogs', 'LogsController@listlogs');

    // datatables
    Route::controller('datatables', 'DatatablesController', [
        'anyData'  => 'datatables.data',
        'getIndex' => 'datatables',
    ]);

    //image upload
    Route::get('/image', 'ImagesController@index');
    Route::post('/uploadimage', 'ImagesController@upload');

    Route::resource('prop', 'DatatablesController');

    //dashboard
    Route::get('dash', 'DashboardController@index');

    //todo - follow ups
    Route::get('todo', 'DashboardController@todo');

    // rubish
    Route::get('/farmbook', 'FarmbookController@index');
    Route::get('/settings', 'SettingsController@index');

});
