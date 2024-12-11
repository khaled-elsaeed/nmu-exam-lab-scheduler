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
     * Generate and return download links for all quiz exports in one file
     *
     * @return array
     */
    public function downloadQuizFiles()
    {
        // Fetch the quiz data based on the session ID, if provided
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

        // Create a single spreadsheet instance
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header row
        $sheet->setCellValue('A1', 'Student Name');
        $sheet->setCellValue('B1', 'University ID');
        $sheet->setCellValue('C1', 'Quiz Name');
        $sheet->setCellValue('D1', 'Lab');
        $sheet->setCellValue('E1', 'Duration');
        $sheet->setCellValue('F1', 'Start Time');
        $sheet->setCellValue('G1', 'End Time');
        $sheet->setCellValue('H1', 'Session Date');

        $row = 2; // Start from the second row for data

        // Loop through the grouped data by quiz ID
        foreach ($data as $quizId => $entries) {
            if ($quizId === 'N/A') {
                continue;
            }

            // Retrieve quiz data and sort by slot number
            $quizData = QuizSlotStudent::query()
                ->where('quiz_id', $quizId)
                ->with(['student', 'quiz', 'slot.lab', 'slot.session'])
                ->get()
                ->sortBy(function ($entry) {
                    return $entry->slot->slot_number;
                });

            // Loop through quiz entries and fill in the sheet
            foreach ($quizData as $entry) {
                $student = $entry->student;
                $quiz = $entry->quiz;
                $slot = $entry->slot;
                $session = $slot->session ?? null;

                // Populate the rows with data
                $sheet->setCellValue("A{$row}", $student->name ?? 'N/A');
                $sheet->setCellValue("B{$row}", $student->academic_id ?? 'N/A');
                $sheet->setCellValue("C{$row}", $quiz->name ?? 'N/A');

                // Lab information
                $building = $slot->lab->building ?? 'N/A';
                $floor = $slot->lab->floor ?? 'N/A';
                $number = $slot->lab->number ?? 'N/A';
                $concatenatedValue = "{$building}-{$floor}-{$number}";
                $sheet->setCellValue("D{$row}", $concatenatedValue);

                // Duration
                $duration = $session ? $session->slot_duration ?? 'N/A' : 'N/A';
                $sheet->setCellValue("E{$row}", $duration);

                // Time formatting
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

                // Format session date
                $sessionDateFormatted = $session ? date('Y-m-d', strtotime($session->date)) : 'N/A';
                $sheet->setCellValue("H{$row}", $sessionDateFormatted);

                // Increment row for next entry
                $row++;
            }
        }

        // Generate a filename
        $filename = "all_quizzes_session_{$this->sessionId}_" . uniqid() . ".xlsx";

        // Send headers and output the file for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        // Write the spreadsheet to the output
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit(); // Prevent further code execution after the file download
    }
}
