<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\QuizSlotStudent;

class ExamExport
{
    public function downloadExcel($quizId = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headings including Session Date
        $sheet->setCellValue('A1', 'Student Name');
        $sheet->setCellValue('B1', 'University ID');
        $sheet->setCellValue('C1', 'Quiz Name');
        $sheet->setCellValue('D1', 'Lab');
        $sheet->setCellValue('E1', 'Duration');
        $sheet->setCellValue('F1', 'Start Time');
        $sheet->setCellValue('G1', 'End Time');
        $sheet->setCellValue('H1', 'Session Date');  // Added new column for Session Date

        // Retrieve data from quiz_slot_student table with eager loading
        $data = QuizSlotStudent::query()
            ->when($quizId, function($query) use ($quizId) {
                return $query->where('quiz_id', $quizId);
            })
            ->with(['student', 'quiz', 'slot.lab', 'slot.session']) // Eager load related models
            ->get();

        // Order the data by slot_number through the slot relationship
        $data = $data->sortBy(function ($entry) {
            return $entry->slot->slot_number;  // Sorting by slot_number
        });

        $row = 2;
        $quizName = null;  // To store quiz name for the filename

        foreach ($data as $entry) {
            // Ensure related data is available to avoid errors
            $student = $entry->student;
            $quiz = $entry->quiz;
            $slot = $entry->slot;
            $session = $slot->session ?? null;

            // Capture the quiz name for the file name
            if (!$quizName && $quiz) {
                $quizName = $quiz->name;
            }

            // Populate the spreadsheet
            $sheet->setCellValue("A{$row}", $student->name ?? 'N/A');
            $sheet->setCellValue("B{$row}", $student->academic_id ?? 'N/A');
            $sheet->setCellValue("C{$row}", $quiz->name ?? 'N/A');

            // Concatenate lab details: Building - Floor - Number
            $building = $slot->lab->building ?? 'N/A';
            $floor = $slot->lab->floor ?? 'N/A';
            $number = $slot->lab->number ?? 'N/A';
            $concatenatedValue = "{$building}-{$floor}-{$number}";
            $sheet->setCellValue("D{$row}", $concatenatedValue);

            // Set Duration (you can customize this field based on your needs, e.g., total duration)
            $duration = $session ? $session->slot_duration ?? 'N/A' : 'N/A';
            $sheet->setCellValue("E{$row}", $duration);

            // Set Start and End Time from the session (convert to 12-hour format)
            $startTime = $session ? $session->start_time ?? 'N/A' : 'N/A';
            $endTime = $session ? $session->end_time ?? 'N/A' : 'N/A';

            if ($startTime !== 'N/A') {
                $startTime = date('h:i A', strtotime($startTime));  // Format to 12-hour time
            }
            if ($endTime !== 'N/A') {
                $endTime = date('h:i A', strtotime($endTime));  // Format to 12-hour time
            }

            $sheet->setCellValue("F{$row}", $startTime);
            $sheet->setCellValue("G{$row}", $endTime);

            // Add Session Date
            $sessionDate = $session ? $session->date ?? 'N/A' : 'N/A';
            $sheet->setCellValue("H{$row}", $sessionDate);

            $row++;
        }

        // Set headers for download, using the quiz name for the filename
        $filename = $quizName ? "{$quizName}_exam_export.xlsx" : "exam_export.xlsx"; // Default filename
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

