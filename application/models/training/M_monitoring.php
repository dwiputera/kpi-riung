<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_monitoring extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_training($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] && $atmp_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] && $mts_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
        }
        return $mts;
    }

    public function get_chart_status($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] && $atmp_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] && $mts_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
        }
        $atmp_mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['atmp_id'], ARRAY_FILTER_USE_BOTH);

        if ($atmp || $mts) {
            $total = count($atmp) + count($mts) - count($atmp_mts);
            $data['total'] = $total;
            $data['done']['value'] = count(array_filter($mts, fn($value, $key) => $value['status'] == 'Y', ARRAY_FILTER_USE_BOTH));
            $data['done']['percentage'] = number_format(($data['done']['value'] / $total) * 100, 2);
            $data['pending']['value'] = count(array_filter($mts, fn($value, $key) => $value['status'] == 'P', ARRAY_FILTER_USE_BOTH)) + count($atmp) - count($atmp_mts);
            $data['pending']['percentage'] = number_format(($data['pending']['value'] / $total) * 100, 2);
            $data['cancel']['value'] = count(array_filter($mts, fn($value, $key) => $value['status'] == 'N', ARRAY_FILTER_USE_BOTH));
            $data['cancel']['percentage'] = number_format(($data['cancel']['value'] / $total) * 100, 2);
            $data['reschedule']['value'] = count(array_filter($mts, fn($value, $key) => $value['status'] == 'R', ARRAY_FILTER_USE_BOTH));
            $data['reschedule']['percentage'] = number_format(($data['reschedule']['value'] / $total) * 100, 2);
            return $data;
        }
    }

    public function get_chart_budget($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] && $atmp_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] && $mts_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
        }
        $atmp_mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['atmp_id'], ARRAY_FILTER_USE_BOTH);

        if ($atmp || $mts) {
            $total = array_sum(array_column($atmp, 'grand_total'));
            $data['total'] = $total;
            $data['grand_total']['value'] = $total;
            $data['grand_total']['percentage'] = number_format(($data['grand_total']['value'] / $total) * 100, 2);
            $data['actual_budget']['value'] = array_sum(array_column($mts, 'actual_budget'));
            $data['actual_budget']['percentage'] = number_format(($data['actual_budget']['value'] / $total) * 100, 2);
            return $data;
        }
    }

    public function get_chart_participants($year, $month, $atmp, $mts, $type = 'ytd')
    {
        if ($type == 'mtd') {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] == $month, ARRAY_FILTER_USE_BOTH);
        } else {
            $atmp = array_filter($atmp, fn($atmp_i, $i_atmp) => $atmp_i['month'] && $atmp_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
            $mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['month'] && $mts_i['month'] <= $month, ARRAY_FILTER_USE_BOTH);
        }
        $atmp_mts = array_filter($mts, fn($mts_i, $i_mts) => $mts_i['atmp_id'], ARRAY_FILTER_USE_BOTH);

        if ($atmp || $mts) {
            $total = array_sum(array_column($atmp, 'total_participants'));
            $data['total'] = $total;
            $data['total_participants']['value'] = $total;
            $data['total_participants']['percentage'] = number_format(($data['total_participants']['value'] / $total) * 100, 2);
            $data['actual_participants']['value'] = array_sum(array_column($mts, 'actual_participants'));
            $data['actual_participants']['percentage'] = number_format(($data['actual_participants']['value'] / $total) * 100, 2);
            return $data;
        }
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
