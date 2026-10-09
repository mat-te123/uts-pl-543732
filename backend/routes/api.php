<?php

use App\Http\Controllers\VacantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(VacantController::class)->group(function(){
    Route::get('/vacants', 'index');
    Route::post('/vacant/post', 'post');
    Route::delete('/vacant/{id}', 'delete');
});
