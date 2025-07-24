<?php
defined('BASEPATH') or exit('No direct script access allowed');

class m_atmp extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($year)
    {
        $query = $this->db->where('year', $year)->order_by('uploaded_at', 'desc')->get('trn_atmp_docs')->result_array();
        return $query;
    }

    public function get_training($year)
    {
        $query = $this->db->query("
            SELECT * FROM trn
            LEFT JOIN (
                SELECT trn_id, count(trn_id) total_participant FROM trn_user
                GROUP BY trn_id
            ) trn_user ON trn_user.trn_id = trn.id
             WHERE year = '$year'
        ")->result_array();
        return $query;
    }

    public function get_training_chart($year)
    {
        $trainings = $this->get_training($year);
        if ($trainings) {
            $total = count($trainings);
            $data['total'] = $total;
            $data['mts']['value'] = count(array_filter($trainings, fn($value, $key) => $value['mts'] == 'Y', ARRAY_FILTER_USE_BOTH));
            $data['mts']['percentage'] = number_format(($data['mts']['value'] / $total) * 100, 2);
            $data['mts_not']['value'] = $total - $data['mts']['value'];
            $data['mts_not']['percentage'] = number_format(($data['mts_not']['value'] / $total) * 100, 2);
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
