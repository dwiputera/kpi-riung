<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_monitoring extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Helper aman untuk menghitung persentase tanpa division by zero
     */
    private function safe_percent($num, $den, $decimals = 2)
    {
        if (empty($den) || $den == 0) {
            return number_format(0, $decimals);
        }
        return number_format(($num / $den) * 100, $decimals);
    }

    public function get_training($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }

        // Filter ATMP based on matching ID with MTS
        $atmp_ids_in_mts = array_column($mts, 'atmp_id');  // Extract all atmp_ids from mts
        $atmp = array_filter($atmp, fn($atmp_i) => in_array($atmp_i['id'], $atmp_ids_in_mts));  // Keep only matching atmp

        $mts_atmp = array_filter($mts, fn($mts_i) => !empty($mts_i['atmp_id']));
        $mts_atmp_atmp_ids  = array_column($mts_atmp, 'atmp_id');
        $atmp_not_mts = array_filter($atmp, fn($atmp_i) => !in_array($atmp_i['id'], $mts_atmp_atmp_ids));

        $trainings = array_merge($mts, $atmp_not_mts);
        return $trainings;
    }

    public function get_chart_status($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }

        // Filter ATMP based on matching ID with MTS
        $atmp_ids_in_mts = array_column($mts, 'atmp_id');  // Extract all atmp_ids from mts
        $atmp = array_filter($atmp, fn($atmp_i) => in_array($atmp_i['id'], $atmp_ids_in_mts));  // Keep only matching atmp

        $atmp_mts = array_filter($mts, fn($mts_i) => !empty($mts_i['atmp_id']));

        $total = count($atmp) + count($mts) - count($atmp_mts);

        $data = [
            'total' => $total,
            'done' => ['value' => 0, 'percentage' => number_format(0, 2)],
            'pending' => ['value' => 0, 'percentage' => number_format(0, 2)],
            'cancel' => ['value' => 0, 'percentage' => number_format(0, 2)],
            'reschedule' => ['value' => 0, 'percentage' => number_format(0, 2)],
        ];

        $data['done']['value']       = count(array_filter($mts, fn($v) => ($v['status'] ?? null) === 'Y'));
        $data['pending']['value']    = count(array_filter($mts, fn($v) => ($v['status'] ?? null) === 'P'));
        $data['reschedule']['value'] = count(array_filter($mts, fn($v) => ($v['status'] ?? null) === 'R'));
        // $data['cancel']['value']     = count(array_filter($mts, fn($v) => ($v['status'] ?? null) === 'N')) + count($atmp) - count($atmp_mts);
        $data['cancel']['value']     = count(array_filter($mts, fn($v) => ($v['status'] ?? null) === 'N'));

        // Persentase aman
        $data['done']['percentage']       = $this->safe_percent($data['done']['value'], $total);
        $data['pending']['percentage']    = $this->safe_percent($data['pending']['value'], $total);
        $data['cancel']['percentage']     = $this->safe_percent($data['cancel']['value'], $total);
        $data['reschedule']['percentage'] = $this->safe_percent($data['reschedule']['value'], $total);

        return $data;
    }

    public function get_chart_budget($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }

        // Filter ATMP based on matching ID with MTS
        $atmp_ids_in_mts = array_column($mts, 'atmp_id');  // Extract all atmp_ids from mts
        $atmp = array_filter($atmp, fn($atmp_i) => in_array($atmp_i['id'], $atmp_ids_in_mts));  // Keep only matching atmp

        $total_atmp = array_sum(array_column($atmp, 'grand_total'));
        $total_mts  = array_sum(array_column($mts,  'grand_total'));

        $data = [
            'total' => $total_atmp,
            'grand_total' => [
                'value' => $total_atmp,
                // Kalau total_atmp > 0, ini akan 100.00; kalau 0, jadikan 0.00 agar aman.
                'percentage' => $this->safe_percent($total_atmp, $total_atmp),
            ],
            'actual_budget' => [
                'value' => $total_mts,
                'percentage' => $this->safe_percent($total_mts, $total_atmp),
            ],
        ];

        return $data;
    }

    public function get_chart_participants($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            // $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts, fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }

        // Filter ATMP based on matching ID with MTS
        $atmp_ids_in_mts = array_column($mts, 'atmp_id');  // Extract all atmp_ids from mts
        $atmp = array_filter($atmp, fn($atmp_i) => in_array($atmp_i['id'], $atmp_ids_in_mts));  // Keep only matching atmp

        $total_atmp_part = array_sum(array_column($atmp, 'total_participants'));
        $total_mts_part  = array_sum(array_column($mts,  'total_participants'));

        $data = [
            'total' => $total_atmp_part,
            'total_participants' => [
                'value' => $total_atmp_part,
                // Sama seperti budget: kalau total 0, persentase 0 (bukan NaN)
                'percentage' => $this->safe_percent($total_atmp_part, $total_atmp_part),
            ],
            'actual_participants' => [
                'value' => $total_mts_part,
                'percentage' => $this->safe_percent($total_mts_part, $total_atmp_part),
            ],
        ];

        return $data;
    }
}
