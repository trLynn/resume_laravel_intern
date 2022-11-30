<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Whoops\Util\TemplateHelper;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => 'ConstantLoader','SetLocale'], function() {

    //Route for Applicant Login and Form Load
    Route::prefix('resume/applicant')->namespace('API\Applicant')->group(function(){
        Route::post("login/{template_id}", "ApplicantController@sendEmailToUser");
        Route::post("check-otp/{email}", "ApplicantController@checkOtp");
        Route::get("form-load/{template_id}/{email}", "ApplicantController@formLoad");
    });

 #Route for status change
 Route::patch('applicant-status/update/{status}','API\Applicant\ApplicantController@update');
 #Route for delete applicants
 Route::delete('applicants/delete','API\Applicant\ApplicantController@delete');
 #Route for levels
 Route::get('template-headings/show','API\Applicant\ApplicantController@showLevels');
    #Route for show all headings
 Route::get('template-headings/show/{template_id}','API\Applicant\ApplicantController@showAllHeadings');

    #Route for admin login
    Route::post('admin/login','API\Employee\EmployeeController@login');
    #Route for applicant pdf view
    Route::get('applicants/view/{applicant_id}','API\Applicant\ApplicantController@view');
    #Route for applicant file download
    Route::get('applicants/download/{applicant_id}','API\Applicant\ApplicantController@download');

    Route::get('/edit/{template_id}', 'API\Template\TemplateController@edit');
    Route::post('/update', 'API\Template\TemplateController@update');

    #Templates Routes
    Route::prefix('templates')->namespace('API\Template')->group(function(){
        Route::get('/types','TemplateController@getType');
        Route::get('/all','TemplateController@all');
        Route::post('/save','TemplateController@store');
        Route::get('/view/{id}','TemplateController@show');
        Route::get('/change-active-status','TemplateController@changeActiveStatus');
        Route::get('edit/{template_id}', 'TemplateController@edit');
        Route::post('update', 'TemplateController@update');
        Route::get('/search', 'TemplateController@search'); //route for template search
        Route::delete('/delete', 'TemplateController@delete'); // route for template delete
        Route::get('/search/template-name', 'TemplateController@templateAllSearch'); //route for template title all
    });
//Route for applicants search 
Route::get('/applicants/search','API\Applicant\ApplicantController@search');
//Route for applicants excel download 
Route::get('/applicants/excel-download','API\Applicant\ApplicantController@excelDownload');



Route::get('applicant/export', [App\Http\Controllers\API\Applicant\ApplicantCreateController::class, 'download'] );

Route::post('applicant/save', [App\Http\Controllers\API\Applicant\ApplicantCreateController::class, 'create']);
Route::put('applicant/update', [App\Http\Controllers\API\Applicant\ApplicantCreateController::class, 'update']);
Route::get('dashboard', 'API\Template\TemplateController@dashboard');
});


Route::get('test', 'TestController@index');


Route::get('test', function(){
    //Route for template edit
    Route::get('templates/edit/{template_id}', 'API\Template\TemplateController@edit');
    //Route for template update
    Route::post('templates/update', 'API\Template\TemplateController@update');

    Route::get('test', 'TestController@show');
});
//Route::get('test', 'TestController@index');


Route::get('test', 'TestController@index');



// Route::get('/test/config', function () {
//     return config('ONE');
// });
