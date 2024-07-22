<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EventController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\LocationController;

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

Route::prefix('locations')->group(function () {
    Route::get('/', [LocationController::class, 'listLocationPagination']);
    Route::get('filter', [LocationController::class, 'filterLocationsByName']);
    Route::get('total', [LocationController::class, 'getTotalLocations']);
    Route::post('/', [LocationController::class, 'save']);
    Route::delete('{id}', [LocationController::class, 'delete']);
    Route::get('{id}', [LocationController::class, 'findById']);
    Route::put('{id}', [LocationController::class, 'update']);
});

Route::prefix('players')->group(function () {
    Route::get('/', [PlayerController::class, 'listPlayersPagination']);
    Route::get('filter', [PlayerController::class, 'filterPlayersByName']);
    Route::get('total', [PlayerController::class, 'getTotalPlayers']);
    Route::post('/', [PlayerController::class, 'save']);
    Route::delete('{id}', [PlayerController::class, 'delete']);
    Route::get('{id}', [PlayerController::class, 'findById']);
    Route::put('{id}', [PlayerController::class, 'update']);
});

Route::prefix('events')->group(function () {
    Route::post('/', [EventController::class, 'createEvent']);   
    Route::get('/', [EventController::class, 'listEventPagination']);
    Route::get('/filter', [EventController::class, 'filterEventsByName']);
    Route::get('/total', [EventController::class, 'getTotalEvents']);
    Route::delete('{id}', [EventController::class, 'delete']);
    Route::get('{id}', [EventController::class, 'findById']);
});

Route::prefix('player-draw')->group(function () {
    Route::post('/', [EventController::class, 'createPlayerDraw']);
});