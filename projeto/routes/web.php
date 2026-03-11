<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/funcionarios', function () {
    return view('funcionarios');
});
