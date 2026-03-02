<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json('Welcome to PetNest to start get to Register or login via https://petnest-production.up.railway.app/api/register or https://petnest-production.up.railway.app/api/login');
});
