<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegistrarController;
use App\Http\Controllers\StudentEnrollmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'home'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'createAccount'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');

Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function (): void {
    Route::get('/enrollments', [StudentEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/create', [StudentEnrollmentController::class, 'create'])->name('enrollments.create');
    Route::post('/enrollments', [StudentEnrollmentController::class, 'store'])->name('enrollments.store');
    Route::patch('/enrollments/{enrollment}/withdraw', [StudentEnrollmentController::class, 'withdraw'])->name('enrollments.withdraw');
    Route::get('/grades', [StudentEnrollmentController::class, 'grades'])->name('grades');
    Route::get('/subjects', [StudentEnrollmentController::class, 'subjects'])->name('subjects');
});

Route::prefix('registrar')->name('registrar.')->middleware(['auth', 'role:registrar'])->group(function (): void {
    Route::get('/applications', [RegistrarController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{enrollment}', [RegistrarController::class, 'review'])->name('applications.review');
    Route::get('/students', [RegistrarController::class, 'students'])->name('students');
    Route::patch('/students/{student}', [RegistrarController::class, 'updateStudent'])->name('students.update');
    Route::patch('/students/{student}/access', [RegistrarController::class, 'toggleStudentAccess'])->name('students.access');
    Route::get('/programs', [RegistrarController::class, 'programs'])->name('programs');
    Route::post('/programs', [RegistrarController::class, 'storeProgram'])->name('programs.store');
    Route::patch('/programs/{program}', [RegistrarController::class, 'updateProgram'])->name('programs.update');
    Route::patch('/programs/{program}/toggle', [RegistrarController::class, 'toggleProgram'])->name('programs.toggle');
    Route::get('/subjects', [RegistrarController::class, 'subjects'])->name('subjects');
    Route::post('/subjects', [RegistrarController::class, 'storeSubject'])->name('subjects.store');
    Route::patch('/subjects/{subject}', [RegistrarController::class, 'updateSubject'])->name('subjects.update');
    Route::patch('/subjects/{subject}/toggle', [RegistrarController::class, 'toggleSubject'])->name('subjects.toggle');
    Route::get('/grades', [RegistrarController::class, 'grades'])->name('grades');
    Route::patch('/grades/{enrollment}/{subject}', [RegistrarController::class, 'updateGrade'])->name('grades.update');
});
