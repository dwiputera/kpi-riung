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
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }
        $mts_atmp           = array_filter($mts, fn($mts_i) => $mts_i['atmp_id'] != null);
        $mts_atmp_atmp_ids  = array_column($mts_atmp, 'atmp_id');
        $atmp_not_mts       = array_filter($atmp, fn($atmp_i) => !in_array($atmp_i['id'], $mts_atmp_atmp_ids));
        $trainings          = array_merge($mts, $atmp_not_mts);
        return $trainings;
    }

    public function get_chart_status($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }
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
        $data['cancel']['value']     = count(array_filter($mts, fn($v) => ($v['status'] ?? null) === 'N')) + count($atmp) - count($atmp_mts);

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
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }
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
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] == $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] == $month);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i) => $atmp_i['month'] && $atmp_i['month'] <= $month);
            $mts  = array_filter($mts,  fn($mts_i)  => $mts_i['month'] && $mts_i['month'] <= $month);
        }

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

    // public function submit()
    // {
    //     $ids = $this->db->query("SELECT id FROM trn")->result_array();
    //     $ids = array_keys(array_column($ids, null, 'id'));
    //     $submitted_data = json_decode($this->input->post('json_data'), true);
    //     foreach ($submitted_data['table_data'] as $key => $training) {
    //         foreach ($ids as $id) {
    //             if ($key == md5($id)) {
    //                 $data[$key] = $training;
    //                 $data[$key]['id'] = $id;
    //             }
    //         }
    //     }
    //     $query = $this->db->update_batch('trn', $data, 'id');
    //     return $query;
    // }
}
