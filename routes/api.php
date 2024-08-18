<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\FournisseurController;
use App\Http\Controllers\Api\LivreurController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

//Authentification Routes
Route::post('/login' , [UserController::class, 'login']);
Route::post('/logout' , [UserController::class, 'logout'])->middleware('auth:sanctum');

//Route de gestion des Fournisseurs
Route::prefix('fournisseurs')->group(function () {
    Route::get('/', [FournisseurController::class, 'index']);
    Route::get('/{id}', [FournisseurController::class, 'show']);
    Route::post('/', [FournisseurController::class, 'store']);
    Route::put('/{id}/update-profile', [FournisseurController::class, 'updateProfile']);
    Route::put('/{id}/update-password', [FournisseurController::class, 'updatePassword']);
    Route::delete('/{id}', [FournisseurController::class, 'delete']);
});

//Route de gestion des Livreurs
Route::prefix('livreurs')->group(function () {
    Route::get('/' , [LivreurController::class, 'index']);
    Route::get('/{id}', [LivreurController::class, 'show']); 
    Route::post('/', [LivreurController::class, 'store']); 
    Route::put('/{id}/update-profile', [LivreurController::class, 'updateProfile']);
    Route::put('/{id}/update-password', [LivreurController::class, 'updatePassword']);
    Route::delete('/{id}', [LivreurController::class, 'delete']);
});

//Route de gestion des Admins
Route::prefix('admins')->group(function (){
    Route::get('/' , [AdminController::class, 'index']);
    Route::post('/' , [AdminController::class, 'store']);
    Route::delete('/{id}' , [AdminController::class, 'delete']);
});

//Route de gestion des Livreurs
Route::prefix('clients')->group(function (){
    Route::get('/' , [ClientController::class, 'index']);
    Route::get('/{id}', [ClientController::class, 'show']);
    Route::post('/', [ClientController::class, 'store']);
    Route::put('/{id}/update-profile', [ClientController::class, 'updateProfile']);
    Route::put('/{id}/update-password', [ClientController::class, 'updatePassword']);
    Route::delete('/{id}', [ClientController::class, 'destroy']);
});
