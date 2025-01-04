<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\QuizSlotStudent;
use Illuminate\Support\Facades\Storage;

class SessionExamExport
{
    protected $sessionId;

    public function __construct($sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    public function downloadQuizFiles()
    {
        $data = QuizSlotStudent::query()
            ->when($this->sessionId, function ($query) {
                $query->whereHas('slot.session', function ($query) {
                    $query->where('id', $this->sessionId);
                });
            })
            ->with(['student', 'quiz.course.faculty', 'slot.lab', 'slot.session'])
            ->get()
            ->groupBy('quiz_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'National ID');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'University ID');
        $sheet->setCellValue('D1', 'Quiz');
        $sheet->setCellValue('E1', 'Faculty');
        $sheet->setCellValue('F1', 'Lab');
        $sheet->setCellValue('G1', 'Duration');
        $sheet->setCellValue('H1', 'Start Time');
        $sheet->setCellValue('I1', 'End Time');
        $sheet->setCellValue('J1', 'Session Date');

        $row = 2;

        foreach ($data as $quizId => $entries) {
            foreach ($entries as $entry) {
                $student = $entry->student;
                $quiz = $entry->quiz;
                $slot = $entry->slot;
                $session = $slot->session ?? null;

                $faculty = $quiz->course->faculty->name ?? 'N/A';

                $building = $slot->lab->building ?? 'N/A';
                $floor = $slot->lab->floor ?? 'N/A';
                $number = $slot->lab->number ?? 'N/A';
                $labDetails = "{$building}-{$floor}-{$number}";

                $duration = $session->slot_duration ?? 'N/A';
                $startTime = $session->start_time ?? null;
                $endTime = $session->end_time ?? null;

                $formattedStartTime = $startTime ? date('h:i A', strtotime($startTime)) : 'N/A';
                $formattedEndTime = $endTime ? date('h:i A', strtotime($endTime)) : 'N/A';
                $sessionDate = $session ? date('Y-m-d', strtotime($session->date)) : 'N/A';

                $sheet->setCellValue("A{$row}", $student->national_id ?? 'N/A');
                $sheet->setCellValue("B{$row}", $student->name ?? 'N/A');
                $sheet->setCellValue("C{$row}", $student->academic_id ?? 'N/A');
                $sheet->setCellValue("D{$row}", $quiz->name ?? 'N/A');
                $sheet->setCellValue("E{$row}", $faculty);
                $sheet->setCellValue("F{$row}", $labDetails);
                $sheet->setCellValue("G{$row}", $duration);
                $sheet->setCellValue("H{$row}", $formattedStartTime);
                $sheet->setCellValue("I{$row}", $formattedEndTime);
                $sheet->setCellValue("J{$row}", $sessionDate);

                $row++;
            }
        }

        $filename = "all_quizzes_session_{$this->sessionId}_" . uniqid() . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}