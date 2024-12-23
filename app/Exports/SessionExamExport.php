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
     * @return void
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
            ->with(['student', 'quiz.course.faculty', 'slot.lab', 'slot.session'])
            ->get()
            ->groupBy('quiz_id');

        // Create a single spreadsheet instance
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header row
        $sheet->setCellValue('A1', 'Student Name');
        $sheet->setCellValue('B1', 'University ID');
        $sheet->setCellValue('C1', 'Quiz');
        $sheet->setCellValue('D1', 'Faculty');
        $sheet->setCellValue('E1', 'Lab');
        $sheet->setCellValue('F1', 'Duration');
        $sheet->setCellValue('G1', 'Start Time');
        $sheet->setCellValue('H1', 'End Time');
        $sheet->setCellValue('I1', 'Session Date');

        $row = 2; // Start from the second row for data

        // Loop through the grouped data by quiz ID
        foreach ($data as $quizId => $entries) {
            foreach ($entries as $entry) {
                $student = $entry->student;
                $quiz = $entry->quiz;
                $slot = $entry->slot;
                $session = $slot->session ?? null;

                // Safely fetch faculty name with null check
                $faculty = $quiz->course->faculty->name ?? 'N/A';

                // Safely fetch lab details
                $building = $slot->lab->building ?? 'N/A';
                $floor = $slot->lab->floor ?? 'N/A';
                $number = $slot->lab->number ?? 'N/A';
                $labDetails = "{$building}-{$floor}-{$number}";

                // Safely fetch duration, start time, end time, and session date
                $duration = $session->slot_duration ?? 'N/A';
                $startTime = $session->start_time ?? null;
                $endTime = $session->end_time ?? null;

                $formattedStartTime = $startTime ? date('h:i A', strtotime($startTime)) : 'N/A';
                $formattedEndTime = $endTime ? date('h:i A', strtotime($endTime)) : 'N/A';
                $sessionDate = $session ? date('Y-m-d', strtotime($session->date)) : 'N/A';

                // Populate the rows with data
                $sheet->setCellValue("A{$row}", $student->name ?? 'N/A');
                $sheet->setCellValue("B{$row}", $student->academic_id ?? 'N/A');
                $sheet->setCellValue("C{$row}", $quiz->name ?? 'N/A');
                $sheet->setCellValue("D{$row}", $faculty);
                $sheet->setCellValue("E{$row}", $labDetails);
                $sheet->setCellValue("F{$row}", $duration);
                $sheet->setCellValue("G{$row}", $formattedStartTime);
                $sheet->setCellValue("H{$row}", $formattedEndTime);
                $sheet->setCellValue("I{$row}", $sessionDate);

                $row++; // Move to the next row
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

