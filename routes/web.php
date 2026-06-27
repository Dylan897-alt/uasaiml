<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\SaveController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AIController::class, 'showForm'])->name('schedular.form');
Route::post('/process', [AIController::class, 'processScheduling'])->name('schedular.process');

Route::post('/save-unit-configuration', [SaveController::class, 'saveUnitConfiguration']
)->name('units.save');

Route::post('/save-technician-configuration', [SaveController::class, 'saveTechnicianConfiguration']
)->name('technicians.save');

// A* Technician Assignment
Route::get('/technician', [AIController::class, 'showTechnicianForm'])->name('technician.form');
Route::post('/technician/process', [AIController::class, 'processTechnician'])->name('technician.process');