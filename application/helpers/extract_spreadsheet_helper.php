<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

function extract_spreadsheet($file_path, $with_sheet_names = false)
{
    $spreadsheet = IOFactory::load($file_path);
    // $sheet = $spreadsheet->getActiveSheet();
    $sheets = $spreadsheet->getSheetNames();
    foreach ($sheets as $i_sheet => $sheet_i) {
        $rows = $spreadsheet->getSheet($i_sheet);;
        $rows = $rows->toArray();
        if ($with_sheet_names === false) {
            $extracted_sheet[$i_sheet] = $rows;
        } else {
            $extracted_sheet[$i_sheet]['name'] = $sheet_i;
            $extracted_sheet[$i_sheet]['rows'] = $rows;
        }
    }

    return $extracted_sheet;
}
