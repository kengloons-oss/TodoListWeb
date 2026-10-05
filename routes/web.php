<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tasks');
Route::get('/dashboard', fn () => redirect()->route('tasks.index'))->middleware('auth')->name('dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/reminders/due', ReminderController::class)->name('reminders.due');
    Route::patch('/reminders/{reminder}/shown', [ReminderController::class, 'markShown'])->name('reminders.shown');
    Route::patch('/tasks/{task}/completion', [TaskController::class, 'toggleCompletion'])->name('tasks.toggle-completion');
    Route::resource('tasks', TaskController::class)->except('show');
});
