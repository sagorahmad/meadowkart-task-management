<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-redis', function () {
    return [
        'php_version' => PHP_VERSION,
        'redis_extension' => extension_loaded('redis'),
        'redis_class' => class_exists('Redis'),
    ];
});

Route::get('/', function () {
    return view('welcome');
});
