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

        // agar urutan dari bawah ke atas seperti di Chart.js
        $labels = array_reverse($rawLabels);

        if (empty($employees)) return [];

        // Filter data valid
        $filtered = array_filter($employees, function ($e) {
            return isset($e['assess_score'], $e['avg_ipa_score'])
                && is_numeric($e['assess_score'])
                && is_numeric($e['avg_ipa_score']);
        });

        if (empty($filtered)) {
            // tidak ada data valid
            foreach ($employees as &$emp) {
                $emp['status'] = null;
            }
            return $employees;
        }
        $potentials   = array_column($filtered, 'assess_score');
        $performances = array_column($filtered, 'avg_ipa_score');

        function floor1($value)
        {
            return floor($value * 10) / 10;
        }

        function ceil1($value)
        {
            return ceil($value * 10) / 10;
        }

        // PAKAI MARGIN
        $xMin = floor1(min($potentials)   - 0.1);
        $xMax = ceil1(max($potentials)    + 0.1);
        $yMin = floor1(min($performances) - 0.1);
        $yMax = ceil1(max($performances)  + 0.1);

        $xStep = ($xMax - $xMin) / 4;
        $yStep = ($yMax - $yMin) / 4;

        // pre-calc buat sedikit micro-optim
        $invXStep = 1 / $xStep;
        $invYStep = 1 / $yStep;

        foreach ($employees as &$emp) {
            if (
                !isset($emp['assess_score'], $emp['avg_ipa_score']) ||
                !is_numeric($emp['assess_score']) ||
                !is_numeric($emp['avg_ipa_score'])
            ) {

                $emp['status'] = null; // atau '(Unknown)'
                continue;
            }

            $x = (float)$emp['assess_score'];
            $y = (float)$emp['avg_ipa_score'];

            // hitung index kolom (0–3) dan baris (0–3) langsung
            $col = (int)floor(($x - $xMin) * $invXStep);
            $row = (int)floor(($y - $yMin) * $invYStep);

            // clamp supaya tetap 0–3
            if ($col < 0 || $col > 3 || $row < 0 || $row > 3) {
                $emp['status'] = '(Unknown)';
            } else {
                $emp['status'] = $labels[$row][$col];
            }
        }

        return $employees;
    }
}
