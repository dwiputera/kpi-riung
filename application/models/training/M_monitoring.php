<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_monitoring extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_training($month, $year = null)
    {
        if ($year) {
            $query = $this->db->query("SELECT * FROM trn WHERE CONCAT(year, '-', LPAD(month, 2, '0')) >= '$year-01' AND CONCAT(year, '-', LPAD(month, 2, '0')) <= '$month' AND mts = 'Y' AND fixed = 'Y'")->result_array();
            return $query;
        } else {
            $query = $this->db->query("SELECT * FROM trn WHERE CONCAT(year, '-', LPAD(month, 2, '0')) = '$month' AND mts = 'Y' AND fixed = 'Y'")->result_array();
        }
        return $query;
    }

    public function get_chart_status($month, $year = null)
    {
        $trainings = $this->get_training($month, $year);
        if ($trainings) {
            $total = count($trainings);
            $data['total'] = $total;
            $data['done']['value'] = count(array_filter($trainings, fn($value, $key) => $value['status'] == 'Y', ARRAY_FILTER_USE_BOTH));
            $data['done']['percentage'] = number_format(($data['done']['value'] / $total) * 100, 2);
            $data['pending']['value'] = count(array_filter($trainings, fn($value, $key) => $value['status'] == 'P', ARRAY_FILTER_USE_BOTH));
            $data['pending']['percentage'] = number_format(($data['pending']['value'] / $total) * 100, 2);
            $data['cancel']['value'] = count(array_filter($trainings, fn($value, $key) => $value['status'] == 'N', ARRAY_FILTER_USE_BOTH));
            $data['cancel']['percentage'] = number_format(($data['cancel']['value'] / $total) * 100, 2);
            $data['reschedule']['value'] = count(array_filter($trainings, fn($value, $key) => $value['status'] == 'R', ARRAY_FILTER_USE_BOTH));
            $data['reschedule']['percentage'] = number_format(($data['reschedule']['value'] / $total) * 100, 2);
            return $data;
        }
    }

    public function get_chart_budget($month, $year = null)
    {
        $trainings = $this->get_training($month, $year);
        if ($trainings) {
            $total = array_sum(array_column($trainings, 'grand_total'));
            $data['total'] = $total;
            $data['grand_total']['value'] = $total;
            $data['grand_total']['percentage'] = number_format(($data['grand_total']['value'] / $total) * 100, 2);
            $data['actual_budget']['value'] = array_sum(array_column($trainings, 'actual_budget'));
            $data['actual_budget']['percentage'] = number_format(($data['actual_budget']['value'] / $total) * 100, 2);
            return $data;
        }
    }

    public function get_chart_participants($month, $year = null)
    {
        $trainings = $this->get_training($month, $year);
        if ($trainings) {
            $total = array_sum(array_column($trainings, 'rmho')) + array_sum(array_column($trainings, 'rmip')) + array_sum(array_column($trainings, 'rebh')) + array_sum(array_column($trainings, 'rmtu')) + array_sum(array_column($trainings, 'rmts')) + array_sum(array_column($trainings, 'rmgm')) + array_sum(array_column($trainings, 'rhml'));
            $data['total'] = $total;
            $data['total_participants']['value'] = $total;
            $data['total_participants']['percentage'] = number_format(($data['total_participants']['value'] / $total) * 100, 2);
            $data['actual_participants']['value'] = array_sum(array_column($trainings, 'actual_participants'));
            $data['actual_participants']['percentage'] = number_format(($data['actual_participants']['value'] / $total) * 100, 2);
            return $data;
        }
    }

    public function submit()
    {
        $ids = $this->db->query("SELECT id FROM trn")->result_array();
        $ids = array_keys(array_column($ids, null, 'id'));
        $submitted_data = json_decode($this->input->post('json_data'), true);
        foreach ($submitted_data['table_data'] as $key => $training) {
            foreach ($ids as $id) {
                if ($key == md5($id)) {
                    $data[$key] = $training;
                    $data[$key]['id'] = $id;
                }
            }
        }
        $query = $this->db->update_batch('trn', $data, 'id');
        return $query;
    }
}
