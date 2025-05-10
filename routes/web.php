<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/redis-test', function () {
    try {
        \Illuminate\Support\Facades\Redis::set('test_key', 'Redis Working!');
        return \Illuminate\Support\Facades\Redis::get('test_key');
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
