<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and are assigned to the "api" middleware group. Make something great!
|
*/

// Define a route for user login
Route::post('/login', [UserController::class, 'login']);

// Protect routes with the 'auth:api' middleware
Route::middleware('auth:api')->group(function () {
    // Define a route for user logout
    Route::get('/logout', [UserController::class, 'Logout']);
});
