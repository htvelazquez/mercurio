<?php
Route::post('contacts', 'SnapshotsController@store')->middleware('x.api.key');

Route::get('v1/ping', 'AlertsController@ping')->middleware('x.api.key');
Route::get('v1/alerts', 'AlertsController@listGet')->middleware('x.api.key');
Route::get('v1/alerts-download', 'AlertsController@listDownload')->middleware('x.api.key');
Route::put('v1/alerts/{id}', 'AlertsController@update')->middleware('x.api.key');
Route::post('v1/alerts/solve', 'AlertsController@bulkSolve')->middleware('x.api.key');
Route::get('v1/company/{linkedinId}', 'AlertsController@getCompanyPage')->middleware('x.api.key');
