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
        $sheet->setCellValue('A1', 'National ID');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'University ID');
        $sheet->setCellValue('D1', 'Quiz Name');
        $sheet->setCellValue('E1', 'Lab');
        $sheet->setCellValue('F1', 'Duration');
        $sheet->setCellValue('G1', 'Start Time');
        $sheet->setCellValue('H1', 'End Time');
        $sheet->setCellValue('I1', 'Session Date');

        $data = QuizSlotStudent::query()
            ->when($quizId, function ($query) use ($quizId) {
                return $query->where('quiz_id', $quizId);
            })
            ->with(['student', 'quiz', 'slot.lab', 'slot.session'])
            ->get();

        $data = $data->sortBy(function ($entry) {
            return $entry->slot->slot_number;
        });

        $row = 2;
        $quiz = $data->first()->quiz ?? null;

        foreach ($data as $entry) {
            $student = $entry->student;
            $quiz = $entry->quiz;
            $slot = $entry->slot;
            $session = $slot->session ?? null;

            $sheet->setCellValue("A{$row}", $student->national_id ?? 'N/A');
            $sheet->setCellValue("B{$row}", $student->name ?? 'N/A');
            $sheet->setCellValue("C{$row}", $student->academic_id ?? 'N/A');
            $sheet->setCellValue("D{$row}", $quiz->name ?? 'N/A');

            $building = $slot->lab->building ?? 'N/A';
            $floor = $slot->lab->floor ?? 'N/A';
            $number = $slot->lab->number ?? 'N/A';
            $concatenatedValue = "{$building}-{$floor}-{$number}";
            $sheet->setCellValue("E{$row}", $concatenatedValue);

            $duration = $session ? $session->slot_duration ?? 'N/A' : 'N/A';
            $sheet->setCellValue("F{$row}", $duration);

            $startTime = $session ? $session->start_time ?? 'N/A' : 'N/A';
            $endTime = $session ? $session->end_time ?? 'N/A' : 'N/A';

            if ($startTime !== 'N/A') {
                $startTime = date('h:i A', strtotime($startTime));
            }
            if ($endTime !== 'N/A') {
                $endTime = date('h:i A', strtotime($endTime));
            }

            $sheet->setCellValue("G{$row}", $startTime);
            $sheet->setCellValue("H{$row}", $endTime);

            $sessionDate = $session ? $session->date ?? 'N/A' : 'N/A';
            $sheet->setCellValue("I{$row}", $sessionDate);

            $row++;
        }

        $filename = $quiz 
            ? "{$quiz->course->code}_" . str_replace(' ', '_', $quiz->course->name) . "_exam_export.xlsx" 
            : "exam_export.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
