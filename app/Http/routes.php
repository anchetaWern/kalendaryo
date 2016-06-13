<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::group(
	['middleware' => ['admin']],
	function(){

		Route::get('/dashboard', 'AdminController@index');

		Route::get('/calendar/create', 'AdminController@createCalendar');
		Route::post('/calendar/create', 'AdminController@doCreateCalendar');

		Route::get('/event/create', 'AdminController@createEvent');
		Route::post('/event/create', 'AdminController@doCreateEvent');

		Route::get('/calendar/sync', 'AdminController@syncCalendar');
		Route::post('/calendar/sync', 'AdminController@doSyncCalendar');

		Route::get('/events', 'AdminController@listEvents');

		Route::get('/logout', 'AdminController@logout');
});

Route::get('/', 'HomeController@index');
Route::get('/login', 'HomeController@login');