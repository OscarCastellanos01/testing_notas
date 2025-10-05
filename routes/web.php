<?php

use App\Http\Controllers\GradeReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/grades');

Route::get('/grades', [GradeReportController::class, 'index'])->name('grades.index');
Route::post('/grades', [GradeReportController::class, 'store'])->name('grades.store');
