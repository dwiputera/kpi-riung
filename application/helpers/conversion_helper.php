<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

function numberToExcelColumn($number)
{
    $column = '';
    while ($number > 0) {
        $number--; // karena Excel dimulai dari 1 (bukan 0)
        $column = chr(65 + ($number % 26)) . $column;
        $number = intval($number / 26);
    }
    return $column;
}

function currencyStringToInteger($currency)
{
    if (!$currency) return null;
    $number = (int) str_replace([",", ".", "Rp", " ", "-"], "", $currency);
    if ($number == 0) return null;
    return $number;
}

function indoMonthToNumber($monthName)
{
    if (!$monthName) return null;
    $monthName = strtoupper($monthName);
    $map = [
        'JANUARI' => '01',
        'FEBRUARI' => '02',
        'MARET' => '03',
        'APRIL' => '04',
        'MEI' => '05',
        'JUNI' => '06',
        'JULI' => '07',
        'AGUSTUS' => '08',
        'SEPTEMBER' => '09',
        'OKTOBER' => '10',
        'NOVEMBER' => '11',
        'DESEMBER' => '12'
    ];

    $upper = strtoupper(trim($monthName));
    return $map[$upper] ?? null;
}
