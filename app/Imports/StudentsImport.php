<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsImport
{
    private $importedStudentIds = [];

    public function import($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];

                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cell = $sheet->getCell($col . $row);
                    $rowData[$col] = $cell->getValue();
                }

                // Validate required fields
                if (empty($rowData['A']) || empty($rowData['B']) || empty($rowData['C'])) {
                    Log::warning("Row {$row} skipped due to missing required data.");
                    continue;
                }

                // Update or create student record
                $student = Student::updateOrCreate(
                    ['academic_id' => $rowData['C']],
                    [
                        'name' => $rowData['B'],
                        'academic_id' => $rowData['C'],
                        'national_id' => $rowData['A'],
                    ]
                );

                // Log successful import
                Log::info("Student imported: ID {$student->id}, Academic ID {$rowData['C']}");

                $this->importedStudentIds[] = $student->id;
            }
        } catch (\Exception $e) {
            Log::error("Failed to import students: " . $e->getMessage());
            throw $e; // Optional: rethrow to let the calling code handle it further
        }
    }

    public function getImportedStudentIds()
    {
        return $this->importedStudentIds;
    }
}
