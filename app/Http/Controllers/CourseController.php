<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'course_name' => 'required|string',
            'course_code' => 'required|string',
            'faculty' => 'nullable|int|exists:faculties,id',  
        ]);

        $facultyId = $request->input('faculty', auth()->user()->getFacultyId());

        if (!$facultyId) {
            return response()->json(['message' => 'User is not associated with any faculty.'], 403);
        }

        try {
            if (Course::whereRaw('LOWER(code) = ?', [strtolower($validated['course_code'])])->exists()) {
                return response()->json(['message' => 'Course code already exists!'], 400);
            }

            if (Course::whereRaw('LOWER(name) = ?', [strtolower($validated['course_name'])])->exists()) {
                return response()->json(['message' => 'Course name already exists!'], 400);
            }

            $course = Course::create([
                'name' => $validated['course_name'],
                'code' => $validated['course_code'],
                'faculty_id' => $facultyId,  
            ]);

            return response()->json(['message' => 'Course created successfully!', 'course' => $course], 201);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating course: ' . $e->getMessage(), [
                'exception' => $e,
                'course_name' => $validated['course_name'],
                'course_code' => $validated['course_code']
            ]);

            return response()->json(['message' => 'Failed to create course. Please try again later.', 'error' => $e->getMessage()], 500);
        }
    }

    // Method to get courses by faculty_id
    public function getCoursesByFaculty($facultyId)
    {
        $courses = Course::where('faculty_id', $facultyId)->get();
        return response()->json($courses);
    }
}
