<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\ExamSettingController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\QuizAssignmentController;
use App\Http\Controllers\StudentHomeController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\DurationSessionController;
use App\Http\Controllers\CourseController;


// Public Routes
Route::get('/', [LoginController::class, 'showLoginPage'])->name('home');
Route::get('/login', [LoginController::class, 'showLoginPage'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Routes for Authenticated Users
Route::middleware(['auth'])->group(function () {
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/home', [AdminHomeController::class, 'showHomePage'])->name('home');

        // Quiz Management
        Route::prefix('quizzes')->group(function () {
            Route::get('/', [QuizController::class, 'index'])->name('quizzes.index');
            Route::get('/create', [QuizController::class, 'create'])->name('quizzes.create');
            Route::post('/', [QuizController::class, 'store'])->name('quizzes.store');
            Route::delete('/{id}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
        });
        
        // Quiz Assignment
        Route::post('/assign-quiz/{courseId}', [QuizAssignmentController::class, 'assignQuizToCourseStudents'])->name('assignQuiz');
    });

    // Student Routes
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/home', [StudentHomeController::class, 'showHomePage'])->name('home');
    });
    
    // Exam Settings Management
    Route::prefix('exam-setting')->group(function () {
        Route::get('/', [ExamSettingController::class, 'getExamSetting']);
        Route::post('/update', [ExamSettingController::class, 'updateExamSetting']);
        Route::get('/view', [ExamSettingController::class, 'index'])->name('exam.index');
        Route::get('/show', [ExamSettingController::class, 'show'])->name('exam-settings.show');
        Route::post('/{id}', [ExamSettingController::class, 'update'])->name('exam-settings.update');
    });

    // Time Slot Management
    Route::prefix('time-slots')->group(function () {
        Route::post('/generate', [TimeSlotController::class, 'generateTimeSlots'])->name('generateTimeSlots');
        Route::get('/', [TimeSlotController::class, 'index'])->name('timeSlots.index');
    });

    // Lab Management
    Route::prefix('labs')->name('labs.')->group(function () {
        Route::get('/', [LabController::class, 'index'])->name('index');
        Route::delete('/{id}', [LabController::class, 'destroy'])->name('destroy');
        Route::put('/{lab}', [LabController::class, 'update'])->name('update');
        Route::post('/labs', [LabController::class, 'store'])->name('store');         // Create a new lab


    });

    Route::get('/admin/quizzes/{quiz}/export', [QuizController::class, 'export'])->name('admin.quizzes.export');


// Route to store a newly created course with a name
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');

// Route to delete a course with a name
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');


    Route::get('sessions', [DurationSessionController::class, 'index'])->name('sessions.index');
    Route::get('sessions/reserve', [DurationSessionController::class, 'reserve'])->name('sessions.reserve');
    Route::delete('sessions/{session}', [DurationSessionController::class, 'destroy'])->name('sessions.destroy');
    

    Route::post('sessions/reserve-quiz-session', [DurationSessionController::class, 'reservePeriodForQuiz'])->name('sessions.reserveForQuiz');

    Route::post('sessions/reverse-reservation-for-quiz', [DurationSessionController::class, 'reverseReservationForQuiz'])->name('sessions.reverseReservationForQuiz');
    Route::post('sessions/acceptReservationForQuiz', [DurationSessionController::class, 'acceptReservationForQuiz'])->name('sessions.acceptReservationForQuiz');

    Route::get('sessions/with-quizzes', [DurationSessionController::class, 'getSessionsWithQuizzes'])->name('sessions.withQuizzes');

    Route::post('sessions/create-exam-period', [DurationSessionController::class, 'createSessionsForExamPeriod'])->name('sessions.createExamPeriod');
});
