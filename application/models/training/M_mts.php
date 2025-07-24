<?php
defined('BASEPATH') or exit('No direct script access allowed');

class m_mts extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $query = $this->db->order_by("uploaded_at", "desc")->get('trn_mts_docs')->result_array();
        return $query;
    }

    public function get_training($month)
    {
        $query = $this->db->query("SELECT * FROM trn WHERE CONCAT(year, '-', LPAD(month, 2, '0')) = '$month' AND mts = 'Y'")->result_array();
        return $query;
    }

    public function get_training_chart($month)
    {
        $trainings = $this->get_training($month);
        if ($trainings) {
            $total = count($trainings);
            $data['total'] = $total;
            $data['fixed']['value'] = count(array_filter($trainings, fn($value, $key) => $value['fixed'] == 'Y', ARRAY_FILTER_USE_BOTH));
            $data['fixed']['percentage'] = number_format(($data['fixed']['value'] / $total) * 100, 2);
            $data['fixed_not']['value'] = $total - $data['fixed']['value'];
            $data['fixed_not']['percentage'] = number_format(($data['fixed_not']['value'] / $total) * 100, 2);
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
