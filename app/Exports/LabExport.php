<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LabExport
{
    protected $sessionId;

    public function __construct($sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    public function downloadLabFiles()
    {
        $data = DB::table('duration_sessions')
            ->join('slots', 'slots.duration_session_id', '=', 'duration_sessions.id')
            ->join('labs', 'slots.lab_id', '=', 'labs.id')
            ->join('quiz_slot_student','quiz_slot_student.slot_id','=','slots.id')
            ->join('quizzes','quizzes.id','=','quiz_slot_student.quiz_id')
            ->join('courses','courses.id','=','quizzes.course_id')
            ->join('faculties','faculties.id','=','courses.faculty_id')
            ->select(
                'duration_sessions.date',
                DB::raw("DATE_FORMAT(slots.start_time, '%h:%i %p') AS formatted_start_time"),
                DB::raw("DATE_FORMAT(slots.end_time, '%h:%i %p') AS formatted_end_time"),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(labs.building, '-', labs.floor, '-', labs.number) ORDER BY labs.building, labs.floor) AS lab_details"),
                DB::raw("GROUP_CONCAT(DISTINCT faculties.name ORDER BY faculties.name) AS faculty_name")
            )
            ->where('slots.current_students', '>', 0)
            ->groupBy(
                'duration_sessions.date',
                'slots.start_time',
                'slots.end_time'
            )
            ->orderBy('duration_sessions.date', 'asc')
            ->orderBy('slots.start_time', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Session Date');
        $sheet->setCellValue('B1', 'Start Time');
        $sheet->setCellValue('C1', 'End Time');
        $sheet->setCellValue('D1', 'Lab Details');
        $sheet->setCellValue('e1', 'faculty Details');

        
        $row = 2;  
        foreach ($data as $entry) {
            $sheet->setCellValue("A{$row}", $entry->date);
            $sheet->setCellValue("B{$row}", $entry->formatted_start_time);
            $sheet->setCellValue("C{$row}", $entry->formatted_end_time);
            $sheet->setCellValue("D{$row}", $entry->lab_details);
            $sheet->setCellValue("E{$row}", $entry->faculty_name);
            $row++;
        }

        $filename = 'lab_sessions.xlsx';
        $writer = new Xlsx($spreadsheet);

        // Output the file to the browser for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
}
