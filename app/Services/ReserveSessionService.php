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
        $session = DurationSession::find($sessionId);

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $quiz = Quiz::find($quizId);

        if (!$quiz) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        $isAlreadyCreated = QuizSlotStudent::where('quiz_id', $quizId)->exists();

        if ($isAlreadyCreated) {
            return response()->json(['error' => 'Quiz has already been reserved in a session!'], 400);
        }

        $students = $quiz->students;
        $quizStudents = $students->count();
        $totalAvailableSpace = 0;

        $slots = $session->slots()
            ->with('lab')
            ->orderBy('slot_number')
            ->get();

        foreach ($slots as $slot) {
            $availableSpace = $slot->max_students - $slot->current_students;
            $totalAvailableSpace += $availableSpace;
        }

        if ($totalAvailableSpace < $quizStudents) {
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

                        $lab = $slot->lab;
                        $labDetails = $lab ? "{$lab->building} - {$lab->floor} - {$lab->number}" : 'N/A';

                        foreach ($students->take($studentsToReserve) as $student) {
                            $reservation = QuizSlotStudent::create([
                                'student_id' => $student->id,
                                'slot_id' => $slot->id,
                                'quiz_id' => $quizId,
                            ]);

                            $reservedStudents[] = $reservation;
                        }

                        $remainingStudents -= $studentsToReserve;
                    }
                }

                if ($remainingStudents <= 0) {
                    break;
                }
            }

            if ($remainingStudents > 0) {
                DB::rollBack();
                return response()->json(['error' => 'Not enough space for all quiz students'], 400);
            }

            DB::commit();

            return response()->json(['success' => 'Quiz students successfully reserved for the session']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("An error occurred during reservation for Quiz ID: $quizId, Session ID: $sessionId. Error: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred during reservation'], 500);
        }
    }

    public function reverseReservation($quizId)
    {
        $reservations = QuizSlotStudent::where('quiz_id', $quizId)->get();
        $quiz = Quiz::find($quizId);

        if (!$quiz) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        if ($reservations->isEmpty()) {
            return response()->json(['error' => 'No reservations found for the quiz'], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($reservations as $reservation) {
                $slot = Slot::find($reservation->slot_id);

                if ($slot) {
                    $slot->current_students -= 1;
                    $slot->save();
                }

                $reservation->delete();
            }

            $quiz->status = 'rejected';
            $quiz->save();

            DB::commit();

            return response()->json(['success' => 'Reservations successfully reversed']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while reversing the reservation', 'message' => $e->getMessage()], 500);
        }
    }

    public function createSessionsForPeriod()
{
    DB::beginTransaction();

    try {
        $examSettings = ExamSetting::first();
        $startDate = Carbon::parse($examSettings->start_date);
        $endDate = Carbon::parse($examSettings->end_date);
        $dailyStartTime = Carbon::parse($examSettings->daily_start_time);
        $dailyEndTime = Carbon::parse($examSettings->daily_end_time);
        $slotDuration = $examSettings->time_slot_duration;
        $restPeriod = $examSettings->rest_period;

        $labs = Lab::orderByRaw('CAST(building AS SIGNED)')
            ->orderByRaw('CAST(floor AS SIGNED)')
            ->orderByRaw('CAST(number AS SIGNED)')
            ->get();

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
                }

                // Add rest period before the next session
                $sessionStartTime = $sessionEndTime->copy()->addMinutes($restPeriod);
                $sessionEndTime = $sessionStartTime->copy()->addMinutes($slotDuration);
            }

            // Move to the next day after processing the current day
            $currentDate->addDay();
        }

        DB::commit();

        return response()->json(['success' => true], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("An error occurred during session creation: " . $e->getMessage());
        return response()->json(['error' => 'An error occurred while creating sessions', 'message' => $e->getMessage()], 500);
    }
}

}
