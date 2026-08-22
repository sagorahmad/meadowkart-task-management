<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;




Route::post('/register',[AuthController::class,'register']);

Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function(){

    Route::post('/tasks',[TaskController::class,'store']);

    Route::get('/tasks',[TaskController::class,'index']);

    Route::get('/tasks/{task}',[TaskController::class,'show']);

    Route::post('/tasks/{task}/cancel',[TaskController::class,'cancel']);

    Route::post('/tasks/{task}/retry',[TaskController::class,'retry']);

});