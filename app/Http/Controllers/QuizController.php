<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Student;
use App\Models\Course;
use App\Models\ExamSetting;
use App\Models\QuizSlotStudent;
use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use App\Exports\ExamExport;
use App\Models\Faculty;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ReserveSessionService;

class QuizController extends Controller
{
    public function index()
    {
        $courses = $this->getCoursesForAuthenticatedFaculty();
        $examDuration = ExamSetting::pluck('time_slot_duration')->first();
        $quizzes = Quiz::with(['course', 'slots.session'])->get();

        return view('admin.quizzes.index', compact('quizzes', 'examDuration', 'courses'));
    }

    private function getCoursesForAuthenticatedFaculty()
    {
        $user = auth()->user();
        
        if ($user && $user->hasRole('faculty')) {
            return Course::whereHas('faculty', function ($query) use ($user) {
                $query->where('name', $this->getFacultyNameForUserRole());
            })->get();
        }

        abort(403, 'Unauthorized access. Only faculty can view courses.');
    }

    private function getFacultyNameForUserRole()
    {
        $user = auth()->user();

        if ($user && $user->hasRole('faculty')) {
            $facultyNameMap = [
                'computer-science-and-engineering' => 'Computer Science & Engineering',
                'faculty_of_business' => 'Business',
                'faculty_of_law' => 'Law',
                'faculty_of_engineering' => 'Engineering',
                'faculty_of_science' => 'Science',
                'faculty_of_medicine' => 'Medicine',
                'faculty_of_dentistry' => 'Dentistry',
                'faculty_of_pharmacy' => 'Pharmacy',
            ];

            foreach ($user->roles as $role) {
                if (isset($facultyNameMap[$role->name])) {
                    return $facultyNameMap[$role->name];
                }
            }

            abort(403, 'No corresponding faculty found for this user role.');
        }

        abort(403, 'Unauthorized user role.');
    }

    public function create()
    {
        return view('admin.quizzes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'students_file' => 'required|file|mimes:xlsx,xls',
        ]);

        if (Quiz::where('course_id', $validated['course_id'])->exists()) {
            return response()->json(['message' => 'Quiz already created for this course.'], 500);
        }

        $quiz = Quiz::create([
            'name' => $validated['title'],
            'course_id' => $validated['course_id'],
            'status' => 'pending',
        ]);

        Log::channel('access')->info('Quiz created by faculty', [
            'user_id' => auth()->user()->username,
            'quiz_id' => $quiz->id,
            'course_id' => $validated['course_id'],
            'title' => $validated['title'],
            'timestamp' => now(),
        ]);

        $this->handleFileUpload($request, $quiz);

        return response()->json([
            'message' => 'Quiz and students have been created and assigned successfully!',
            'quiz' => $quiz,
            'course' => Course::find($validated['course_id']),
        ], 201);
    }

    private function handleFileUpload(Request $request, Quiz $quiz)
    {
        $facultyName = $this->getFacultyNameForUserRole();
        $facultyFolderPath = storage_path('app/temp_uploads/' . Str::slug($facultyName));

        if (!file_exists($facultyFolderPath)) {
            mkdir($facultyFolderPath, 0777, true);
        }

        $this->importStudentsFromExcel($request->file('students_file'));

        $this->assignStudentsToQuiz($quiz);

        $this->storeUploadedFile($request, $facultyFolderPath);
    }

    private function importStudentsFromExcel($file)
    {
        $import = new StudentsImport();
        $import->import($file);
    }

    private function assignStudentsToQuiz(Quiz $quiz)
    {
        $students = Student::all();
        $quiz->students()->sync($students->pluck('id'));
    }

    private function storeUploadedFile(Request $request, $facultyFolderPath)
    {
        $uploadedFile = $request->file('students_file');
        $timestamp = now()->format('Ymd_His');
        $courseSlug = Str::slug(Course::find($request->course_id)->name);
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileName = $timestamp . '_' . $courseSlug . '.' . $extension;

        $filePath = $uploadedFile->move($facultyFolderPath, $fileName);

        Log::channel('access')->info('File uploaded by faculty', [
            'faculty' => $facultyFolderPath,
            'user_id' => auth()->user()->username,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'timestamp' => now(),
        ]);
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);

        if (QuizSlotStudent::where('quiz_id', $id)->exists()) {
            $reserveSessionService = app(ReserveSessionService::class);
            $reserveSessionService->reverseReservation($id);
        }

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    public function export($id)
    {
        $examExport = new ExamExport();
        return $examExport->downloadExcel($id);
    }
}
