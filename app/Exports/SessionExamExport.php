<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\QuizSlotStudent;
use Illuminate\Support\Facades\Storage;

class SessionExamExport
{
    protected $sessionId;

    /**
     * Constructor to set the session ID
     *
     * @param int|null $sessionId
     */
    public function __construct($sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Generate and return download links for all quiz exports
     *
     * @return array
     */
    public function downloadQuizFiles()
    {
        $data = QuizSlotStudent::query()
            ->when($this->sessionId, function ($query) {
                $query->whereHas('slot.session', function ($query) {
                    $query->where('id', $this->sessionId);
                });
            })
            ->with(['student', 'quiz', 'slot.lab', 'slot.session'])
            ->get()
            ->groupBy(function ($entry) {
                return $entry->quiz->id ?? 'N/A';
            });

        $downloadLinks = [];

        foreach ($data as $quizId => $entries) {
            if ($quizId === 'N/A') {
                continue;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Student Name');
            $sheet->setCellValue('B1', 'University ID');
            $sheet->setCellValue('C1', 'Quiz Name');
            $sheet->setCellValue('D1', 'Lab');
            $sheet->setCellValue('E1', 'Duration');
            $sheet->setCellValue('F1', 'Start Time');
            $sheet->setCellValue('G1', 'End Time');
            $sheet->setCellValue('H1', 'Session Date');

            $quizData = QuizSlotStudent::query()
                ->where('quiz_id', $quizId)
                ->with(['student', 'quiz', 'slot.lab', 'slot.session'])
                ->get()
                ->sortBy(function ($entry) {
                    return $entry->slot->slot_number;
                });

            $row = 2;
            $quizName = null;
            $sessionDate = null;

            foreach ($quizData as $entry) {
                $student = $entry->student;
                $quiz = $entry->quiz;
                $slot = $entry->slot;
                $session = $slot->session ?? null;

                if (!$quizName && $quiz) {
                    $quizName = $quiz->name;
                }
                if (!$sessionDate && $session) {
                    $sessionDate = $session->date;
                }

                $sheet->setCellValue("A{$row}", $student->name ?? 'N/A');
                $sheet->setCellValue("B{$row}", $student->academic_id ?? 'N/A');
                $sheet->setCellValue("C{$row}", $quiz->name ?? 'N/A');

                $building = $slot->lab->building ?? 'N/A';
                $floor = $slot->lab->floor ?? 'N/A';
                $number = $slot->lab->number ?? 'N/A';
                $concatenatedValue = "{$building}-{$floor}-{$number}";
                $sheet->setCellValue("D{$row}", $concatenatedValue);

                $duration = $session ? $session->slot_duration ?? 'N/A' : 'N/A';
                $sheet->setCellValue("E{$row}", $duration);

                $startTime = $session ? $session->start_time ?? 'N/A' : 'N/A';
                $endTime = $session ? $session->end_time ?? 'N/A' : 'N/A';

                if ($startTime !== 'N/A') {
                    $startTime = date('h:i A', strtotime($startTime)); // Format to 12-hour time
                }
                if ($endTime !== 'N/A') {
                    $endTime = date('h:i A', strtotime($endTime));
                }

                $sheet->setCellValue("F{$row}", $startTime);
                $sheet->setCellValue("G{$row}", $endTime);

                $sessionDateFormatted = $sessionDate ? date('Y-m-d', strtotime($sessionDate)) : 'N/A';
                $sheet->setCellValue("H{$row}", $sessionDateFormatted);

                $row++;
            }

            $quizNameSanitized = preg_replace('/[^a-zA-Z0-9-_]/', '_', $quizName);
            $sessionDateSanitized = preg_replace('/[^a-zA-Z0-9-_]/', '_', $sessionDateFormatted);

            $filename = "quiz_{$quizNameSanitized}_session_{$sessionDateSanitized}.xlsx";
            $path = storage_path("app/public/quiz_exports/{$filename}");

            if (!file_exists(storage_path('app/public/quiz_exports'))) {
                mkdir(storage_path('app/public/quiz_exports'), 0777, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            $downloadLinks[] = asset("storage/quiz_exports/{$filename}");
        }

        return $downloadLinks;
    }
}
