<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get("/registration", [AuthController::class,"showRegistrationForm"])->name("auth.registrationView");
Route::post('/registration', [AuthController::class, 'registration'])->name('register');

Route::get('/login', [AuthController::class, "showLoginForm"])->name('auth.loginView');
Route::post('/login', [AuthController::class,'login'])->name('auth.login');

Route::get('/home', [AuthController::class,'showHomePage'])->name('home');