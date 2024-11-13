<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\QuizSlotStudent;
use Illuminate\Support\Facades\Storage;

class LabExport
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

    public function downloadLabFiles()
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
                return $entry->slot->lab->id ?? 'N/A';
            });

        $downloadLinks = [];

        foreach ($data as $labId => $entries) {
            if ($labId === 'N/A') {
                continue;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Lab Location');
            $sheet->setCellValue('B1', 'Quiz Name');
            $sheet->setCellValue('C1', 'Student Name');
            $sheet->setCellValue('D1', 'University ID');

            $firstEntry = $entries->first();
            $lab = $firstEntry->slot->lab;
            $labLocation = "{$lab->building} - {$lab->floor} - {$lab->number}";

            $session = $firstEntry->slot->session;
            $sessionDate = $session->date ?? 'N/A';
            $startTime = $session->start_time ?? 'N/A';
            $endTime = $session->end_time ?? 'N/A';

            $sessionDateFormatted = $sessionDate ? date('Y-m-d', strtotime($sessionDate)) : 'N/A';
            $startTimeFormatted = $startTime ? date('h:i A', strtotime($startTime)) : 'N/A';
            $endTimeFormatted = $endTime ? date('h:i A', strtotime($endTime)) : 'N/A';

            $labLocationSanitized = preg_replace('/[^a-zA-Z0-9-_]/', '_', $labLocation);
            $sessionDateSanitized = preg_replace('/[^a-zA-Z0-9-_]/', '_', $sessionDateFormatted);
            $startTimeSanitized = preg_replace('/[^a-zA-Z0-9-_]/', '_', $startTimeFormatted);
            $endTimeSanitized = preg_replace('/[^a-zA-Z0-9-_]/', '_', $endTimeFormatted);

            $filename = "lab_{$labLocationSanitized}_date_{$sessionDateSanitized}_time_{$startTimeSanitized}_to_{$endTimeSanitized}.xlsx";
            $path = storage_path("app/public/lab_exports/{$filename}");

            if (!file_exists(storage_path('app/public/lab_exports'))) {
                mkdir(storage_path('app/public/lab_exports'), 0777, true);
            }

            $row = 2;
            foreach ($entries as $entry) {
                $quizName = $entry->quiz->name ?? 'N/A';
                $studentName = $entry->student->name ?? 'N/A';
                $universityId = $entry->student->academic_id ?? 'N/A';

                $sheet->setCellValue("A{$row}", $labLocation);
                $sheet->setCellValue("B{$row}", $quizName);
                $sheet->setCellValue("C{$row}", $studentName);
                $sheet->setCellValue("D{$row}", $universityId);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            $downloadLinks[] = asset("storage/lab_exports/{$filename}");
        }

        return $downloadLinks;
    }
}
