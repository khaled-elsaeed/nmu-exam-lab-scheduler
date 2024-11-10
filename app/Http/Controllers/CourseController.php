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
        $validated = $request->validate([
            'course_name' => 'required|string',
            'course_code' => 'required|string',
            'faculty' => 'required|int',
        ]);

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
                'faculty_id' => $validated['faculty'],
            ]);

            return response()->json(['message' => 'Course created successfully!', 'course' => $course], 201);
        } catch (\Exception $e) {
            Log::error('Error creating course: ' . $e->getMessage(), [
                'exception' => $e,
                'course_name' => $validated['course_name'],
                'course_code' => $validated['course_code']
            ]);

            return response()->json(['message' => 'Failed to create course. Please try again later.', 'error' => $e->getMessage()], 500);
        }
    }

    private function getFacultyIdForUserRole($validated)
    {
        $user = auth()->user();

        try {
            if ($user && $user->hasRole('faculty')) {
                $facultyMap = [
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
                    if (isset($facultyMap[$role->name])) {
                        $faculty = Faculty::where('name', $facultyMap[$role->name])->first();
                        if (!$faculty) {
                            throw new \Exception("Faculty not found.");
                        }
                        return $faculty->id;
                    }
                }
                throw new \Exception('No corresponding faculty found for this user role.');
            }

            throw new \Exception('Unauthorized user role.');
        } catch (\Exception $e) {
            Log::error('Error getting faculty ID for user role: ' . $e->getMessage(), [
                'user' => $user ? $user->id : 'unknown',
                'roles' => $user ? $user->roles->pluck('name') : 'no roles'
            ]);
            throw $e; 
        }
    }


    // In your controller
    public function getCoursesByFaculty($facultyId)
    {
        $courses = Course::where('faculty_id', $facultyId)->get();
        return response()->json($courses);
    }

}
