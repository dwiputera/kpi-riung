<?php

defined('BASEPATH') or exit('No direct script access allowed');

class M_hav extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function calculate_hav_status(array $employees): array
    {
        $rawLabels = [
            ['Solid Contributor', 'Promotable', 'Prostar 2', 'Top Talent'],
            ['Solid Contributor', 'Promotable', 'Promotable', 'Prostar 1'],
            ['Solid Contributor', 'Promotable', 'Promotable', 'Promotable'],
            ['Unfit', 'Sleeping Tiger', 'Sleeping Tiger', 'Sleeping Tiger']
        ];

        $labels = array_reverse($rawLabels); // agar urutan dari bawah ke atas seperti di Chart.js

        if (empty($employees)) return [];

        // Hitung batas minimum & maksimum
        $filtered = array_filter($employees, function ($e) {
            return isset($e['assess_score'], $e['avg_ipa_score']) && is_numeric($e['assess_score']) && is_numeric($e['avg_ipa_score']);
        });

        $potentials = array_column($filtered, 'assess_score');
        $performances = array_column($filtered, 'avg_ipa_score');

        $xMin = floor(min($potentials) - 5);
        $xMax = ceil(max($potentials) + 5);
        $yMin = floor(min($performances) - 5);
        $yMax = ceil(max($performances) + 5);

        $xStep = ($xMax - $xMin) / 4;
        $yStep = ($yMax - $yMin) / 4;

        // Tentukan status untuk tiap karyawan
        foreach ($employees as &$emp) {
            if (!isset($emp['assess_score'], $emp['avg_ipa_score']) || !is_numeric($emp['assess_score']) || !is_numeric($emp['avg_ipa_score'])) {
                $emp['status'] = null; // atau "(Unknown)"
                continue;
            }

            $x = floatval($emp['assess_score']);
            $y = floatval($emp['avg_ipa_score']);
            $emp['status'] = '(Unknown)';

            for ($i = 0; $i < 4; $i++) {
                for ($j = 0; $j < 4; $j++) {
                    $xStart = $xMin + $j * $xStep;
                    $xEnd = $xStart + $xStep;
                    $yStart = $yMin + $i * $yStep;
                    $yEnd = $yStart + $yStep;

                    if ($x >= $xStart && $x < $xEnd && $y >= $yStart && $y < $yEnd) {
                        $emp['status'] = $labels[$i][$j];
                        break 2;
                    }
                }
            }
        }

        return $employees;
    }
}
