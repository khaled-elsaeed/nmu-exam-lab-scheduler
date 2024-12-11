<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\ExamSettingController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizAssignmentController;
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
            Route::get('/{courseId}', [QuizController::class, 'getQuizzesByCourse'])->name('quizez.by.course');
            Route::get('/stats/{quizId}',[QuizController::class,'getQuizStudentCount'])->name('quizzes.student-counts');
        });

        // Quiz Assignment
        Route::post('/assign-quiz/{courseId}', [QuizAssignmentController::class, 'assignQuizToCourseStudents'])->name('assignQuiz');
    });


    // Exam Settings Management
    Route::prefix('exam-setting')->group(function () {
        Route::get('/', [ExamSettingController::class, 'getExamSetting']);
        Route::post('/update', [ExamSettingController::class, 'updateExamSetting']);
        Route::get('/view', [ExamSettingController::class, 'index'])->name('exam.index');
        Route::get('/show', [ExamSettingController::class, 'show'])->name('exam-settings.show');
        Route::post('/{id}', [ExamSettingController::class, 'update'])->name('exam-settings.update');
    });



    // Lab Management
    Route::prefix('labs')->name('labs.')->group(function () {
        Route::get('/', [LabController::class, 'index'])->name('index');
        Route::delete('/{id}', [LabController::class, 'destroy'])->name('destroy');
        Route::put('/{lab}', [LabController::class, 'update'])->name('update');
        Route::post('/labs', [LabController::class, 'store'])->name('store');  // Create a new lab
    });

    // Course Routes
    Route::prefix('courses')->group(function () {
        Route::post('/', [CourseController::class, 'store'])->name('courses.store');
        Route::delete('/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('/{facultyId}', [CourseController::class, 'getCoursesByFaculty'])->name('get.courses.by.faculty');
    });

    // Quiz Export
    Route::get('/admin/quizzes/{quiz}/export', [QuizController::class, 'export'])->name('admin.quizzes.export');
    Route::get('/admin/courses/{course}/quizzes', [QuizController::class, 'getQuizzesByCourse'])->name('admin.courses.quizzes');

    // Session Routes
    Route::prefix('sessions')->group(function () {
        Route::get('/', [DurationSessionController::class, 'index'])->name('sessions.index');
        Route::get('reserve', [DurationSessionController::class, 'reserve'])->name('sessions.reserve');
        Route::delete('/{session}', [DurationSessionController::class, 'destroy'])->name('sessions.destroy');
        Route::get('/export-labs', [DurationSessionController::class, 'exportSessionLabs'])
    ->name('sessions.exportLabs');
    Route::get('/labs/{sessionId}', [DurationSessionController::class, 'getSessionLabs'])->name('sessions.labs');

    Route::get('/export-quizzes', [DurationSessionController::class, 'exportSessionQuizzes'])
    ->name('sessions.exportQuizzes');


        // Session Quiz Reservations
        Route::post('reserve-quiz-session', [DurationSessionController::class, 'reservePeriodForQuiz'])->name('sessions.reserveForQuiz');
        Route::post('reverse-reservation-for-quiz', [DurationSessionController::class, 'reverseReservationForQuiz'])->name('sessions.reverseReservationForQuiz');
        Route::post('sessions/acceptReservationForQuiz', [DurationSessionController::class, 'acceptReservationForQuiz'])->name('sessions.acceptReservationForQuiz');

        // Get Sessions with Quizzes
        Route::get('sessions/with-quizzes', [DurationSessionController::class, 'getSessionsWithQuizzes'])->name('sessions.withQuizzes');

        // Create Exam Period Sessions
        Route::post('sessions/create-exam-period', [DurationSessionController::class, 'createSessionsForExamPeriod'])->name('sessions.createExamPeriod');
    });
});
