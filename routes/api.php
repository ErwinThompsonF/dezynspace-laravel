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


    Route::post('/admin/register', 'UserController@RegisterAdmin');
    Route::post('/admin/login', 'UserController@LoginAdmin');

    Route::post('/register', 'UserController@Register');
    Route::post('/login', 'UserController@Login');

    // EARTH
    Route::get('countries', 'CountryController@Countries');
    Route::get('asia', 'CountryController@CountriesAsia');
    Route::post('city', 'CountryController@City');
    Route::post('/checkout', 'PaypalController@createPayment')->name('create-payment');
    Route::get('/confirm', 'PaypalController@confirmPayment')->name('confirm-payment');

    Route::group(['middleware' => 'auth:api'], function () {


        
        Route::group(['middleware' => 'scope:Admin'], function () {

            Route::get('/atoken', function () {
                return ['message' => 'Token is valid'];
            });
            // DESIGNER
            Route::post('designer', 'UserController@Create');
            Route::post('designer/schedule', 'UserController@CreateSchedule');
            Route::get('designers', 'UserController@Read');
            Route::get('designer/{id}', 'UserController@UpdateShow');
            Route::put('designer/{id}', 'UserController@Update');
            Route::put('designer/schedule/{id}', 'UserController@UpdateSchedule');

            // BOOKINGS
            Route::get('bookings', 'BookingController@Read');
            Route::get('booking/{id}', 'BookingController@UpdateShow');
            Route::put('booking/{id}', 'BookingController@Update');

            // QUESTIONS
            Route::post('question', 'QuestionController@Create');
            Route::get('questions', 'QuestionController@Read');
            Route::put('question/{id}', 'QuestionController@Update');
        });

        Route::group(['middleware' => 'scope:Clients'], function () {

            Route::get('/ctoken', function () {
                return ['message' => 'Token is valid'];
            });

            Route::post('booking', 'BookingController@Create');

        });
    });
