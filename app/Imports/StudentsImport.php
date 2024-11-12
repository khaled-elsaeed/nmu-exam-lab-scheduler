<?php

namespace App\Imports;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsImport
{
    private $importedStudentIds = [];

    public function import($file)
{
    $spreadsheet = IOFactory::load($file->getRealPath());
    $sheet = $spreadsheet->getActiveSheet();
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    // Start from row 1, assuming no headers
    for ($row = 1; $row <= $highestRow; $row++) {
        $rowData = [];

        // Loop through each column for the current row
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cell = $sheet->getCell($col . $row);
            $rowData[$col] = $cell->getValue();
        }

        // Validate the row data (optional)
        if (empty($rowData['A']) || empty($rowData['B']) || empty($rowData['C'])) {
            continue; // Skip rows with missing data
        }

        // Update or create the student record
        $student = Student::updateOrCreate(
            ['academic_id' => $rowData['C']],
            [
                'name' => $rowData['B'],
                'academic_id' => $rowData['C'],
                'national_id' => $rowData['A'],
            ]
        );

        // Store the student ID of the imported student
        $this->importedStudentIds[] = $student->id;
    }
}


    public function getImportedStudentIds()
    {
        return $this->importedStudentIds;
    }
}
