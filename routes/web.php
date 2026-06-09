<?php

use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AIController::class, 'showForm'])->name('schedular.form');
Route::post('/process', [AIController::class, 'processScheduling'])->name('schedular.process');