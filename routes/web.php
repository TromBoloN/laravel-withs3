<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('employees.index');
});

Route::resource('employees', EmployeeController::class)->except(['show']);

Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
