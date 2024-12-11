<?php

namespace App\Http\Controllers;

use App\Services\ReserveSessionService;
use App\Models\DurationSession;
use App\Models\Lab;

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
            Log::channel('access')->info('Sessions created for exam period', [
                'user_id' => auth()->user()->username,
                'timestamp' => now(),
            ]);

            return $this->reserveSessionService->createSessionsForPeriod();

            Log::channel('access')->info('Quiz created by faculty', [
                'user_id' => auth()->user()->username,
                'quiz_id' => $quiz->id,
                'course_id' => $validated['course_id'],
                'title' => $validated['title'],
                'faculty_id' => $facultyId,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating sessions for exam period: ' . $e->getMessage(), [
                'exception' => $e,
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
                'exception' => $e,
            ]);
            return response()->json(['error' => 'An error occurred while retrieving sessions. Please try again later.'], 500);
        }
    }

    public function reserve()
    {
        try {
            Log::channel('access')->info('Attempt to reserve session for quiz', [
                'user_id' => auth()->user()->username,
                'quiz_id' => $quiz->id ?? 'N/A',
                'timestamp' => now(),
            ]);
            $labs = Lab::all();
            $faculties = Faculty::all();

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

            return view('admin.sessions.reserve', compact('sessions', 'totalSessions', 'totalSlots', 'results', 'faculties', 'quizzes','labs'));
        } catch (\Exception $e) {
            Log::error('Error retrieving reservation data: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return response()->json(['error' => 'An error occurred while retrieving reservation data. Please try again later.'], 500);
        }
    }

    public function reservePeriodForQuiz(Request $request)
{
    try {
        // Validate required parameters
        $validated = $request->validate([
            'session_id' => 'required|integer',
            'quiz_id' => 'required|integer',
            'building_order' => 'required|array', 
            'specific_labs' => 'nullable'
        ]);

        // Retrieve parameters
        $sessionId = $request->input('session_id');
        $quizId = $request->input('quiz_id');
        $buildingOrder = $request->input('building_order');
        $specificLabs = $request->input('specific_labs');

        return $this->reserveSessionService->reserveForQuiz($sessionId, $quizId, $buildingOrder,$specificLabs);

    } catch (\Exception $e) {
        Log::error('Error reserving period for quiz: ' . $e->getMessage(), [
            'exception' => $e,
        ]);
        return response()->json(['error' => 'An error occurred while reserving the period. Please try again later.'], 500);
    }
}


    

    public function reverseReservationForQuiz(Request $request)
    {
        try {
            Log::channel('access')->info('reverse quiz reservation', [
                'user_id' => auth()->user()->username,
                'timestamp' => now(),
            ]);
            $quizId = $request->input('quiz_id');
            if (!$quizId) {
                return response()->json(['error' => 'Quiz ID is required'], 400);
            }

            return $this->reserveSessionService->reverseReservation($quizId);
        } catch (\Exception $e) {
            Log::error('Error reversing reservation for quiz: ' . $e->getMessage(), [
                'exception' => $e,
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
                $quizzes = $session->slots
                    ->flatMap(function ($slot) {
                        return $slot->quizzes;
                    })
                    ->unique('id');

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
                'exception' => $e,
            ]);
            return response()->json(['error' => 'An error occurred while retrieving sessions with quizzes. Please try again later.'], 500);
        }
    }

    public function acceptReservationForQuiz(Request $request)
    {
        try {
            $request->validate([
                'quiz_id' => 'required|integer',
            ]);

            $quiz = Quiz::find($request->quiz_id);

            if (!$quiz) {
                Log::warning('Quiz not found', [
                    'user_id' => auth()->user()->username,
                    'quiz_id' => $request->quiz_id,
                ]);
                return response()->json(['error' => 'Quiz not found.'], 404);
            }

            $quiz->status = 'accepted';
            $quiz->save();

            Log::channel('access')->info('Quiz accepted by faculty', [
                'user_id' => auth()->user()->username,
                'quiz_id' => $quiz->id,
                'course_id' => $quiz->course_id ?? 'N/A',
                'title' => $quiz->title ?? 'N/A',
                'timestamp' => now(),
            ]);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Error accepting reservation for quiz', [
                'user_id' => auth()->user()->username ?? 'guest',
                'quiz_id' => $request->quiz_id ?? 'N/A',
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'An error occurred while accepting the reservation. Please try again later.'], 500);
        }
    }

    public function exportSessionLabs()
    {
        $labExport = new LabExport();

        $downloadLinks = $labExport->downloadLabFiles();

        return response()->json(['downloadLinks' => $downloadLinks]);
    }
public function exportSessionQuizzes()
{
    try {
        $quizExport = new SessionExamExport();
        $downloadLinks = $quizExport->downloadQuizFiles();

        if (empty($downloadLinks)) {
            return response()->json(['error' => 'No quizzes found for export'], 404);
        }

        return response()->json(['downloadLinks' => $downloadLinks]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred during export', 'message' => $e->getMessage()], 500);
    }
}


    public function getSessionLabs($sessionId)
    {
        try {
            $session = DurationSession::with(['slots.lab'])->findOrFail($sessionId);

            $labs = $session->slots->map(function ($slot) {
                $lab = $slot->lab;
                return [
                    'lab_id' => $lab->id,
                    'lab_name' => $lab->number,
                    'capacity' => $lab->capacity,
                    'location' => $lab->building . ' - ' . $lab->floor . ' - ' . $lab->number,
                    'current_capacity' => $slot->current_students,
                ];
            });

            return response()->json([
                'success' => true,
                'labs' => $labs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching session lab details: ' . $e->getMessage(),
            ]);
        }
    }
}
