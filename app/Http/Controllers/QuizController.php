<?php
namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Student;
use App\Models\Course;
use App\Models\ExamSetting;
use App\Models\QuizSlotStudent;
use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use App\Exports\ExamExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ReserveSessionService;

class QuizController extends Controller
{
    public function index()
    {
        $faculties  = Faculty::all();
        $examDuration = ExamSetting::pluck('time_slot_duration')->first();
        $quizzes = Quiz::with(['course', 'slots.session'])->get();

        return view('admin.quizzes.index', compact('quizzes', 'examDuration', 'faculties'));
    }

    private function getCoursesForAuthenticatedFaculty()
    {
        $user = auth()->user();
        
        if ($user && $user->hasRole('faculty')) {
            return Course::whereHas('faculty', function ($query) use ($user) {
                $query->where('id', $user->getFacultyId());  // Fetch faculty_id directly from the user model
            })->get();
        }

        abort(403, 'Unauthorized access. Only faculty can view courses.');
    }

    public function create()
    {
        return view('admin.quizzes.create');
    }

    public function store(Request $request)
{
    // Validate request
    $validated = $request->validate([
        'course_id' => 'required|exists:courses,id',
        'title' => 'required|string|max:255',
        'students_file' => 'required|file|mimes:xlsx,xls',
        'faculty' => 'nullable|exists:faculties,id', // Optional faculty, must exist if provided
    ]);

    // If faculty is not provided in the request, use the authenticated user's faculty
    $facultyId = $request->input('faculty', auth()->user()->getFacultyId());

    if (!$facultyId) {
        return response()->json(['message' => 'User is not associated with any faculty.'], 403);
    }

    // Check if a quiz already exists for the given course
    if (Quiz::where('course_id', $validated['course_id'])->exists()) {
        return response()->json(['message' => 'Quiz already created for this course.'], 500);
    }

    // Create the quiz
    $quiz = Quiz::create([
        'name' => $validated['title'],
        'course_id' => $validated['course_id'],
        'status' => 'pending',
        'faculty_id' => $facultyId,  
    ]);

    // Log quiz creation
    Log::channel('access')->info('Quiz created by faculty', [
        'user_id' => auth()->user()->username,
        'quiz_id' => $quiz->id,
        'course_id' => $validated['course_id'],
        'title' => $validated['title'],
        'faculty_id' => $facultyId,
        'timestamp' => now(),
    ]);

    // Handle file upload
    $this->handleFileUpload($request, $quiz);

    return response()->json([
        'message' => 'Quiz and students have been created and assigned successfully!',
        'quiz' => $quiz,
        'course' => Course::find($validated['course_id']),
    ], 201);
}


private function handleFileUpload(Request $request, Quiz $quiz)
{
    // Get the faculty of the authenticated user
    $facultyId = auth()->user()->getFacultyId();
    $faculty = Faculty::find($facultyId);  // Retrieve faculty by ID

    if ($faculty) {
        // Use the faculty name from the found faculty record
        $facultyName = $faculty->name;
    } else {
        // Fallback if faculty is not found
        $facultyName = 'unknown_faculty';
    }

    // Prepare faculty folder path
    $facultyFolderPath = storage_path('app/temp_uploads/' . Str::slug($facultyName));

    // Create the folder if it doesn't exist
    if (!file_exists($facultyFolderPath)) {
        mkdir($facultyFolderPath, 0777, true);
    }

    // Proceed with importing students from Excel and capture the student IDs
    $importedStudentIds = $this->importStudentsFromExcel($request->file('students_file'));

    // Assign the imported students to the quiz
    $this->assignStudentsToQuiz($quiz, $importedStudentIds);

    // Store the uploaded file in the appropriate folder
    $this->storeUploadedFile($request, $facultyFolderPath);
}

private function importStudentsFromExcel($file)
{
    $import = new StudentsImport();
    $import->import($file);

    // Return the IDs of the imported students
    return $import->getImportedStudentIds();  // This now returns the IDs of the imported students
}

private function assignStudentsToQuiz(Quiz $quiz, $studentIds)
{
    // Sync the quiz with the imported student IDs
    $quiz->students()->sync($studentIds);
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

    public function getQuizzesByCourse(Course $course)
    {
        // Fetch quizzes related to the given course
        $quizzes = $course->quizzes; // Assuming Course has a 'quizzes' relationship

        if ($quizzes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No quizzes available for this course.',
            ]);
        }

        // Return quizzes data in JSON format
        return response()->json([
            'success' => true,
            'quizzes' => $quizzes,
        ]);
    }
}

