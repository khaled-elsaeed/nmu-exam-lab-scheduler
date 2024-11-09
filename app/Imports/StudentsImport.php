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

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $sheet->getCell($col . $row);
                $rowData[$col] = $cell->getValue();
            }

            $student = Student::updateOrCreate(
                ['academic_id' => $rowData['C']],
                [
                    'name' => $rowData['B'],
                    'academic_id' => $rowData['C'],
                    'national_id' => $rowData['A'],
                ]
            );

            $this->importedStudentIds[] = $student->id;
        }
    }

    public function getImportedStudentIds()
    {
        return $this->importedStudentIds;
    }
}
