<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'TicketController@index');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('manage/tickets', 'HomeController@closedTickets');
Route::get('manage/user', 'HomeController@users');
Route::get('manage/department', 'HomeController@department');
Route::get('manage/user/add', 'HomeController@userAdd');
Route::post('manage/user/add', 'HomeController@storeUser');
Route::post('search', 'HomeController@search');
Route::get('manage/user/{id}', 'HomeController@editUser');
Route::post('manage/user/edit/{id}', 'HomeController@updateUser');

Route::get('view', 'TicketController@support');
Route::post('view', 'TicketController@loadTicket');
Route::post('support', 'TicketController@storeTicket');
Route::get('tickets', 'TicketController@tickets');
Route::get('ticket/{tid}', 'TicketController@viewTicket');
Route::post('ticket/{tid}', 'TicketController@storeReply');
Route::get('ticket/close/{tid}', 'TicketController@close');
Route::get('manage/settings', 'HomeController@settings');
Route::post('manage/settings', 'HomeController@updateSettings');
Route::get('manage/ticket/delete/{tid}', 'HomeController@deleteTicket');

Route::get('profile', 'HomeController@profile');
Route::post('profile', 'HomeController@updateProfile');
Route::post('profile/password', 'HomeController@updateProfilePassword');
Route::get('admin/ticket/{tid}', 'HomeController@viewTicket');
Route::post('admin/ticket/{tid}', 'HomeController@storeReply');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
