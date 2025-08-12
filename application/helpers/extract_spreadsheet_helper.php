<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

function extract_spreadsheet($file_path, $with_sheet_names = false, $start_sheet_index = 0)
{
    ini_set('memory_limit', '2048M'); // naikkan sesuai kebutuhan
    $reader = IOFactory::createReaderForFile($file_path);
    $reader->setReadDataOnly(true); // hemat memori

    $spreadsheet = $reader->load($file_path);
    $sheets = $spreadsheet->getSheetNames();
    $extracted_sheet = [];

    $maxRows = 5000;
    $rowIndex = 0;
    for ($i_sheet = $start_sheet_index; $i_sheet < count($sheets); $i_sheet++) {
        $sheet = $spreadsheet->getSheet($i_sheet);
        $rows = [];

        foreach ($sheet->getRowIterator() as $row) {
            if ($rowIndex >= $maxRows) break;
            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true); // HEMAT MEMORI!

            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->isFormula()
                    ? $cell->getOldCalculatedValue()
                    : $cell->getValue();
            }

            $rows[] = $rowData;
            $rowIndex++;
        }

        $extracted_sheet[$i_sheet] = $with_sheet_names
            ? ['name' => $sheets[$i_sheet], 'rows' => $rows]
            : $rows;
    }

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    gc_collect_cycles(); // Optional: bersihkan memory lebih agresif

    return $extracted_sheet;
}
