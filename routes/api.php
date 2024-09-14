<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FournisseurController;
use App\Http\Controllers\Api\LivreurController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Authentification Routes
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUser']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Route de gestion des Fournisseurs
Route::prefix('fournisseurs')->group(function () {
    Route::get('/', [FournisseurController::class, 'index']);
    Route::get('/{id}', [FournisseurController::class, 'show']);
    Route::post('/', [AuthController::class, 'storeFournisseur']);
    Route::put('/{id}/update-profile', [FournisseurController::class, 'updateProfile']);
    Route::put('/{id}/update-password', [FournisseurController::class, 'updatePassword']);
    Route::delete('/{id}', [FournisseurController::class, 'delete']);
    Route::middleware('auth:sanctum')->post('/create-livreur', [FournisseurController::class, 'createLivreur']);
    Route::middleware('auth:sanctum')->get('/fournisseur/has-livreur', [FournisseurController::class, 'hasLivreur']);
});

//Routes de gestion des courses
Route::prefix('courses')->group(function () {
    Route::middleware("auth:sanctum")->post('/create-course', [CourseController::class, 'store']);
    Route::middleware("auth:sanctum")->get('/', [CourseController::class, 'index']);
    Route::middleware('auth:sanctum')->post('/accept', [CourseController::class, 'acceptCourse']);
});

//Route de gestion des Livreurs
Route::prefix('livreurs')->group(function () {
    Route::get('/', [LivreurController::class, 'index']);
    Route::get('/{id}', [LivreurController::class, 'show']);
    Route::post('/', [AuthController::class, 'storeLivreur']);
    Route::put('/{id}/update-profile', [LivreurController::class, 'updateProfile']);
    Route::put('/{id}/update-password', [LivreurController::class, 'updatePassword']);
    Route::delete('/{id}', [LivreurController::class, 'delete']);
});

//Route de gestion des Admins
Route::prefix('admins')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::post('/', [AuthController::class, 'storeAdmin']);
    Route::delete('/{id}', [AdminController::class, 'delete']);
});

//Route de gestion des Clients
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::get('/{id}', [ClientController::class, 'show']);
    Route::post('/', [AuthController::class, 'storeClient']);
    Route::put('/{id}/update-profile', [ClientController::class, 'updateProfile']);
    Route::put('/{id}/update-password', [ClientController::class, 'updatePassword']);
    Route::delete('/{id}', [ClientController::class, 'destroy']);
});

//Routes des messages
Route::post("/messages", [MessageController::class, 'store']);
