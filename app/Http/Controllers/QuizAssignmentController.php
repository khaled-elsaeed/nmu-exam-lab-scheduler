<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\TimeSlot;
use App\Models\QuizAssignment;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizAssignmentController extends Controller
{
    public function assignQuizToCourseStudents(Request $request)
    {
        try {
            $validated = $request->validate([
                'quizId' => 'required|exists:quizzes,id',
            ]);

            $quizId = $validated['quizId'];
            $quiz = Quiz::with('course')->find($quizId);

            if (!$quiz || !$quiz->course) {
                return response()->json(['message' => 'Quiz or associated course not found.'], 404);
            }

            $course = $quiz->course;
            $students = $this->getStudentsForCourse($course);

            if ($students->isEmpty()) {
                return response()->json(['message' => 'No students found for this course.'], 404);
            }

            $availableTimeSlots = $this->getAvailableTimeSlots();

            if ($availableTimeSlots->isEmpty()) {
                return $this->noTimeSlotsAvailableResponse();
            }

            $assignments = $this->assignQuizzesToStudents($students, $availableTimeSlots, $quizId, $course);

            return response()->json(['message' => 'Quiz assigned successfully.', 'assignments' => $assignments]);

        } catch (\Exception $e) {
            Log::error("Error assigning quiz: " . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while assigning quizzes.'], 500);
        }
    }

    private function getStudentsForCourse(Course $course)
    {
        return $course->students()->with('quizzes')->get();
    }

    private function getAvailableTimeSlots()
    {
        return TimeSlot::where('current_students', '<', DB::raw('max_students'))
                       ->whereNotNull('current_students')
                       ->whereNotNull('max_students')
                       ->get();
    }

    private function noTimeSlotsAvailableResponse()
    {
        return response()->json(['message' => 'No available time slots for the quiz.'], 404);
    }

    private function assignQuizzesToStudents($students, $availableTimeSlots, $quizId, $course)
    {
        $assignments = [];
        $unassignedStudents = []; // Track unassigned students
        
        foreach ($students as $student) {
            $assigned = false;

            foreach ($availableTimeSlots as $timeSlot) {
                if ($timeSlot->current_students >= $timeSlot->max_students) {
                    continue;
                }

                if ($this->isStudentAssignedToTimeSlot($student, $timeSlot)) {
                    continue;
                }

                if (!$this->isStudentAlreadyAssigned($student, $timeSlot, $course, $quizId)) {
                    $this->createQuizAssignment($student, $course, $timeSlot, $quizId);
                    $this->incrementCurrentStudents($timeSlot);
                    $assignments[] = $this->formatAssignment($student, $course, $timeSlot);
                    $assigned = true;
                    break;
                }
            }

            if (!$assigned) {
                $unassignedStudents[] = $student;
            }
        }

        if (count($unassignedStudents) > 0) {
            Log::warning('Students could not be assigned to any time slot.', [
                'students' => $unassignedStudents->pluck('id')->toArray()
            ]);
        }

        return $assignments;
    }

    private function isStudentAssignedToTimeSlot(Student $student, TimeSlot $timeSlot)
    {
        return QuizAssignment::where('student_id', $student->id)
                             ->where('time_slot_id', $timeSlot->id)
                             ->exists();
    }

    private function isStudentAlreadyAssigned(Student $student, TimeSlot $timeSlot, Course $course, $quizId)
    {
        return QuizAssignment::where('student_id', $student->id)
                             ->where('course_id', $course->id)
                             ->where('quiz_id', $quizId)
                             ->exists();
    }

    private function createQuizAssignment(Student $student, Course $course, TimeSlot $timeSlot, $quizId)
    {
        QuizAssignment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'quiz_id' => $quizId,
        ]);
    }

    private function incrementCurrentStudents(TimeSlot $timeSlot)
    {
        $timeSlot->increment('current_students');
    }

    private function formatAssignment(Student $student, Course $course, TimeSlot $timeSlot)
    {
        return [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'time_slot_id' => $timeSlot->id,
            'date' => $timeSlot->date,
            'start_time' => $timeSlot->start_time,
            'end_time' => $timeSlot->end_time,
        ];
    }
}
