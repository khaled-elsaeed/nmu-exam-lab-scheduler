<?php

namespace App\Http\Controllers;

use App\Services\ReserveSessionService;
use App\Models\DurationSession;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Exports\LabExport;
use App\Exports\SessionExamExport;


use Illuminate\Support\Facades\Log;

class DurationSessionController extends Controller
{
    protected $reserveSessionService;

    public function __construct(ReserveSessionService $reserveSessionService)
    {
        $this->reserveSessionService = $reserveSessionService;
    }

    public function createSessionsForExamPeriod(Request $request)
    {
        try {
            return $this->reserveSessionService->createSessionsForPeriod();
        } catch (\Exception $e) {
            Log::error('Error creating sessions for exam period: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while creating the sessions. Please try again later.'], 500);
        }
    }

    public function index()
    {
        try {
            $sessions = DurationSession::with('labs', 'slots')->get();
            $totalSessions = $sessions->count();
            $sessionData = [];

            foreach ($sessions as $session) {
                $maxOccupants = $session->slots->sum('max_students');
                $taken = $session->slots->sum('current_students');
                $remaining = $maxOccupants - $taken;

                $sessionData[] = [
                    'session' => $session,
                    'max_occupants' => $maxOccupants,
                    'taken' => $taken,
                    'remaining' => $remaining,
                ];
            }

            return view('admin.sessions.index', compact('sessionData', 'totalSessions'));
        } catch (\Exception $e) {
            Log::error('Error retrieving sessions: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while retrieving sessions. Please try again later.'], 500);
        }
    }

    public function reserve()
    {
        try {
            $faculties  = Faculty::all();

            $quizzes = Quiz::all();
            $sessions = DurationSession::with('labs', 'slots')->get();
            $totalSessions = $sessions->count();
            $results = [];

            foreach ($sessions as $session) {
                $totalTimeTaken = null;
                if ($session->start_time && $session->end_time) {
                    $startTime = Carbon::parse($session->start_time);
                    $endTime = Carbon::parse($session->end_time);
                    $totalTimeTaken = $startTime->diffInMinutes($endTime);
                }

                $totalMaxOccupantsForSession = $session->slots->sum('max_students');
                $totalTakenForSession = $session->slots->sum('current_students');
                $sessionDate = Carbon::parse($session->date)->format('Y-m-d');

                if (!isset($results[$sessionDate])) {
                    $results[$sessionDate] = [
                        'date' => $sessionDate,
                        'sessions' => [],
                        'total_max_occupants' => 0,
                        'total_taken' => 0,
                        'total_time_taken' => 0,
                    ];
                }

                $results[$sessionDate]['sessions'][] = [
                    'session' => $session,
                    'total_time_taken' => $totalTimeTaken,
                    'total_max_occupants' => $totalMaxOccupantsForSession,
                    'total_taken' => $totalTakenForSession,
                ];

                $results[$sessionDate]['total_max_occupants'] += $totalMaxOccupantsForSession;
                $results[$sessionDate]['total_taken'] += $totalTakenForSession;
                if ($totalTimeTaken) {
                    $results[$sessionDate]['total_time_taken'] += $totalTimeTaken;
                }
            }

            $totalSlots = $sessions->sum(function ($session) {
                return $session->slots->count();
            });

            return view('admin.sessions.reserve', compact('sessions', 'totalSessions', 'totalSlots', 'results','faculties', 'quizzes'));
        } catch (\Exception $e) {
            Log::error('Error retrieving reservation data: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while retrieving reservation data. Please try again later.'], 500);
        }
    }

    public function reservePeriodForQuiz(Request $request)
    {
        try {
            return $this->reserveSessionService->reserveForQuiz(
                $request->input('session_id'),
                $request->input('quiz_id')
            );
        } catch (\Exception $e) {
            Log::error('Error reserving period for quiz: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while reserving the period. Please try again later.'], 500);
        }
    }

    public function reverseReservationForQuiz(Request $request)
    {
        try {
            $quizId = $request->input('quiz_id');
            if (!$quizId) {
                return response()->json(['error' => 'Quiz ID is required'], 400);
            }

            return $this->reserveSessionService->reverseReservation($quizId);
        } catch (\Exception $e) {
            Log::error('Error reversing reservation for quiz: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while reversing the reservation. Please try again later.'], 500);
        }
    }

    public function getSessionsWithQuizzes()
    {
        try {
            $sessions = DurationSession::with(['slots.quizzes'])->get();

            $groupedSessions = [];

            foreach ($sessions as $session) {
                $quizzes = $session->slots->flatMap(function ($slot) {
                    return $slot->quizzes;
                })->unique('id');

                $date = Carbon::parse($session->date)->toDateString();

                if (!isset($groupedSessions[$date])) {
                    $groupedSessions[$date] = [];
                }

                $groupedSessions[$date][] = [
                    'session' => $session,
                    'quizzes' => $quizzes,
                ];
            }

            return view('admin.sessions.reservations', compact('groupedSessions'));
        } catch (\Exception $e) {
            Log::error('Error retrieving sessions with quizzes: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while retrieving sessions with quizzes. Please try again later.'], 500);
        }
    }

    public function acceptReservationForQuiz(Request $request)
    {
        try {
            $quiz = Quiz::find($request->quiz_id);

            if ($quiz) {
                $quiz->status = 'accepted';
                $quiz->save();

                return response()->json(['success' => true], 200);
            }

            return response()->json(['error' => 'Quiz not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error accepting reservation for quiz: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'An error occurred while accepting the reservation. Please try again later.'], 500);
        }
    }

    public function exportSessionLabs($sessionId)
{
    $labExport = new LabExport($sessionId);
    
    // Call the method to generate the Excel file for each lab and return the link
    $downloadLinks = $labExport->downloadLabFiles();

    // Return the download link(s) in a JSON format
    return response()->json(['downloadLinks' => $downloadLinks]);
}

public function exportSessionQuizzes($sessionId)
{
    // Create an instance of the ExamExport class with the session ID
    $quizExport = new SessionExamExport($sessionId);
    
    // Call the method to generate the Excel file for each quiz and return the link
    $downloadLinks = $quizExport->downloadQuizFiles();

    // Return the download link(s) in a JSON format
    return response()->json(['downloadLinks' => $downloadLinks]);
}

    
}
