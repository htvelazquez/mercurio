<?php

// Chrome Extension
Route::post('extension/auth', 'ExtensionController@auth');
Route::get('extension/job', 'ExtensionController@get');
Route::post('extension/job/{JobsContactsId}', 'ExtensionController@post');

// Ping
Route::middleware('api.key')->get('v1/ping', 'JobController@ping');

// Client
Route::middleware('api.key')->get('v1/clients', 'ClientController@get');
Route::middleware('api.key')->put('v1/clients/{id}', 'ClientController@put');

// Jobs
Route::middleware('api.key')->get('v1/dashboard', 'JobController@dashboard');
Route::middleware('api.key')->get('v1/jobs/{StatusId}/{PriorityId}', 'JobController@get');
Route::middleware('api.key')->post('v1/jobs', 'JobController@post');