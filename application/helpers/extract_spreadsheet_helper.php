<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

function extract_spreadsheet($file_path, $with_sheet_names = false)
{
    $reader = IOFactory::createReaderForFile($file_path);
    $reader->setReadDataOnly(false); // Biar bisa cek formula
    $spreadsheet = $reader->load($file_path);

    $sheets = $spreadsheet->getSheetNames();
    $extracted_sheet = [];

    foreach ($sheets as $i_sheet => $sheet_name) {
        $sheet = $spreadsheet->getSheet($i_sheet);
        $rows = [];

        foreach ($sheet->getRowIterator() as $row) {
            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                if ($cell->isFormula()) {
                    $rowData[] = $cell->getOldCalculatedValue(); // Nilai cached
                } else {
                    $rowData[] = $cell->getValue();
                }
            }
            $rows[] = $rowData;
        }

        if ($with_sheet_names === false) {
            $extracted_sheet[$i_sheet] = $rows;
        } else {
            $extracted_sheet[$i_sheet] = [
                'name' => $sheet_name,
                'rows' => $rows
            ];
        }
    }

    return $extracted_sheet;
}
