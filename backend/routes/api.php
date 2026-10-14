<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\VacantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('user/register', [UserController::class, 'register']);
Route::post('user/login', [UserController::class, 'login']);



Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::delete('/user/{id}', [UserController::class, 'delete']);
    Route::controller(VacantController::class)->group(function(){
        Route::get('/vacants', 'index');
        Route::post('/vacant/post', 'post');
        Route::delete('/vacant/{id}', 'delete');
        Route::put('/vacant/{id}', 'update');
    });
});
