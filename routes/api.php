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

// Route::resource('/worksheets', 'AssignmentsController', [
//     'except' => ['edit','store','show']
// ]);
// Find school (clever)
Route::post('/findschool', 'SchoolController@findSchool');

// Webhooks
Route::post('/webhooks/shopify', 'WebhooksController@shopify');
Route::get('/webhooks/shopify/get', 'WebhooksController@shopifyGet');

// Worksheets
// Route::post('/worksheets/{id}/{projectid}/','WorksheetController@store');
// Route::get('/worksheets/{id}/{projectid}/','WorksheetController@get');
