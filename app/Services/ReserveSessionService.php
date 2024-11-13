<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\DurationSession;
use App\Models\Slot;
use App\Models\ExamSetting;
use App\Models\Lab;
use App\Models\QuizSlotStudent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReserveSessionService
{
    public function reserveForQuiz($sessionId, $quizId)
    {
        Log::info("Starting reservation process for Quiz ID: {$quizId}, Session ID: {$sessionId}");
    
        $session = DurationSession::find($sessionId);
        if (!$session) {
            Log::error("Session not found for ID: {$sessionId}");
            return response()->json(['error' => 'Session not found'], 404);
        }
    
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            Log::error("Quiz not found for ID: {$quizId}");
            return response()->json(['error' => 'Quiz not found'], 404);
        }
    
        $isAlreadyCreated = QuizSlotStudent::where('quiz_id', $quizId)->exists();
        if ($isAlreadyCreated) {
            Log::warning("Quiz with ID: {$quizId} has already been reserved in a session.");
            return response()->json(['error' => 'Quiz has already been reserved in a session!'], 400);
        }
    
        $students = $quiz->students->all(); // Convert to array for array_shift usage
        $quizStudents = count($students);
        $totalAvailableSpace = 0;
    
        Log::info("Logging all students for Quiz ID: {$quizId}");
        foreach ($students as $student) {
            Log::info("Student ID: {$student->id}, Name: {$student->name}");
        }
    
        Log::info("Total number of students for Quiz ID {$quizId}: {$quizStudents}");
    
        $slots = $session->slots()
            ->with('lab')
            ->orderBy('slot_number','desc')
            ->get();

    
        foreach ($slots as $slot) {
            $availableSpace = $slot->max_students - $slot->current_students;
            $totalAvailableSpace += $availableSpace;
            Log::info("Slot ID: {$slot->id}, Available Space: {$availableSpace}, Total Available Space: {$totalAvailableSpace}");
        }
    
        if ($totalAvailableSpace < $quizStudents) {
            Log::error("Not enough available space for Quiz ID {$quizId}. Required: {$quizStudents}, Available: {$totalAvailableSpace}");
            return response()->json(['error' => 'Not enough available space for the quiz students'], 400);
        }
    
        DB::beginTransaction();
    
        try {
            $remainingStudents = $quizStudents;
            $reservedStudents = [];
    
            foreach ($slots as $slot) {
                if ($remainingStudents > 0) {
                    $availableSpace = $slot->max_students - $slot->current_students;
                    if ($availableSpace > 0) {
                        $studentsToReserve = min($remainingStudents, $availableSpace);
                        $slot->current_students += $studentsToReserve;
                        $slot->save();
    
                        Log::info("Reserving {$studentsToReserve} students for Slot ID: {$slot->id}");
    
                        $lab = $slot->lab;
                        $labDetails = $lab ? "{$lab->building} - {$lab->floor} - {$lab->number}" : 'N/A';
                        Log::info("Lab details: {$labDetails}");
    
                        // Reserve each student and remove from the list
                        for ($i = 0; $i < $studentsToReserve; $i++) {
                            $student = array_shift($students); // Get and remove the next student
                            if (!$student) break;
    
                            $reservation = QuizSlotStudent::create([
                                'student_id' => $student->id,
                                'slot_id' => $slot->id,
                                'quiz_id' => $quizId,
                            ]);
    
                            Log::info("Reserved Student ID: {$student->id}, Name: {$student->name} for Slot ID: {$slot->id}");
    
                            $reservedStudents[] = $reservation;
                        }
    
                        $remainingStudents -= $studentsToReserve;
                    }
                }
    
                if ($remainingStudents <= 0) {
                    Log::info("All students have been successfully reserved.");
                    break;
                }
            }
    
            if ($remainingStudents > 0) {
                DB::rollBack();
                Log::error("Not enough space for all quiz students. Rolling back all reservations.");
                return response()->json(['error' => 'Not enough space for all quiz students'], 400);
            }
    
            DB::commit();
            Log::info("Successfully reserved all students for Quiz ID: {$quizId}");
    
            return response()->json(['success' => 'Quiz students successfully reserved for the session']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("An error occurred during reservation for Quiz ID: {$quizId}, Session ID: {$sessionId}. Error: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred during reservation'], 500);
        }
    }
    

    


    public function reverseReservation($quizId)
    {
        Log::info("Reversing reservation for Quiz ID: {$quizId}");

        $reservations = QuizSlotStudent::where('quiz_id', $quizId)->get();
        $quiz = Quiz::find($quizId);

        if (!$quiz) {
            Log::error("Quiz not found for ID: {$quizId}");
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        if ($reservations->isEmpty()) {
            Log::warning("No reservations found for Quiz ID: {$quizId}");
            return response()->json(['error' => 'No reservations found for the quiz'], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($reservations as $reservation) {
                $slot = Slot::find($reservation->slot_id);

                if ($slot) {
                    $slot->current_students -= 1;
                    $slot->save();
                    Log::info("Slot ID: {$slot->id} updated. Current students: {$slot->current_students}");
                }

                $reservation->delete();
                Log::info("Deleted reservation for Student ID: {$reservation->student_id} in Slot ID: {$reservation->slot_id}");
            }

            $quiz->status = 'rejected';
            $quiz->save();
            Log::info("Quiz ID: {$quizId} status updated to 'rejected'.");

            DB::commit();

            return response()->json(['success' => 'Reservations successfully reversed']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("An error occurred while reversing reservation for Quiz ID: {$quizId}. Error: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while reversing the reservation', 'message' => $e->getMessage()], 500);
        }
    }

    public function createSessionsForPeriod()
    {
        Log::info("Starting session creation for the period.");

        DB::beginTransaction();

        try {
            $examSettings = ExamSetting::first();
            $startDate = Carbon::parse($examSettings->start_date);
            $endDate = Carbon::parse($examSettings->end_date);
            $dailyStartTime = Carbon::parse($examSettings->daily_start_time);
            $dailyEndTime = Carbon::parse($examSettings->daily_end_time);
            $slotDuration = $examSettings->time_slot_duration;
            $restPeriod = $examSettings->rest_period;

            Log::info("Exam settings loaded. Start date: {$startDate}, End date: {$endDate}, Daily Start Time: {$dailyStartTime}, Daily End Time: {$dailyEndTime}, Slot Duration: {$slotDuration}, Rest Period: {$restPeriod}");

            $labs = Lab::orderByRaw('CAST(building AS SIGNED)')
                ->orderByRaw('CAST(floor AS SIGNED)')
                ->orderByRaw('CAST(number AS SIGNED)')
                ->get();

            Log::info("Labs retrieved: " . $labs->count() . " labs available.");

            $currentDate = $startDate;

            while ($currentDate <= $endDate) {
                // Skip Fridays (dayOfWeek 5 represents Friday)
                if ($currentDate->dayOfWeek == 5) {
                    $currentDate->addDay();  // Skip to next day
                    continue;
                }

                $sessionStartTime = $dailyStartTime->copy();
                $sessionEndTime = $sessionStartTime->copy()->addMinutes($slotDuration);

                while ($sessionEndTime <= $dailyEndTime) {
                    $session = DurationSession::create([
                        'date' => $currentDate->toDateString(),
                        'start_time' => $sessionStartTime->toTimeString(),
                        'end_time' => $sessionEndTime->toTimeString(),
                        'slot_duration' => $slotDuration,
                    ]);
                    Log::info("Session created for date: {$currentDate->toDateString()}, Start Time: {$sessionStartTime->toTimeString()}, End Time: {$sessionEndTime->toTimeString()}");

                    $slotNumber = 1;
                    foreach ($labs as $lab) {
                        $slot = Slot::create([
                            'lab_id' => $lab->id,
                            'duration_session_id' => $session->id,
                            'slot_number' => $slotNumber++,
                            'start_time' => $sessionStartTime->toTimeString(),
                            'end_time' => $sessionEndTime->toTimeString(),
                            'max_students' => $lab->capacity,
                        ]);
                        Log::info("Slot created for Lab ID: {$lab->id}, Slot Number: {$slot->slot_number}");
                    }

                    // Add rest period before the next session
                    $sessionStartTime = $sessionEndTime->copy()->addMinutes($restPeriod);
                    $sessionEndTime = $sessionStartTime->copy()->addMinutes($slotDuration);
                }

                // Move to the next day after processing the current day
                $currentDate->addDay();
            }

            DB::commit();
            Log::info("Session creation process completed successfully.");

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("An error occurred during session creation: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while creating sessions', 'message' => $e->getMessage()], 500);
        }
    }
}
