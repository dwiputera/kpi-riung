<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_mts_user extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_mts_user($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT *, trn_mts_user.id id FROM trn_mts_user
            LEFT JOIN rml_sso_la.users sso_usrs ON sso_usrs.NRP = trn_mts_user.NRP
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_user_not_trn($mts_id)
    {
        $query = $this->db->query("
            SELECT * FROM rml_sso_la.users
            WHERE NRP NOT IN (
                SELECT NRP FROM trn_mts_user WHERE mts_id = $mts_id 
            )
        ")->result_array();
        return $query;
    }

    public function add($mts_id)
    {
        $success = false;
        $data_inserts = [];
        $trn_mts_users = $this->get_mts_user($mts_id, "mts_id");
        $input_nrp = $this->input->post('NRP') ?? [];
        $data_deletes = array_filter($trn_mts_users, fn($tu_i, $i_tu) => !in_array($tu_i['NRP'], $input_nrp), ARRAY_FILTER_USE_BOTH);
        if ($data_deletes) {
            $success = $this->db->where_in('id', array_column($data_deletes, 'id'))->delete('trn_mts_user');
        }
        if ($input_nrp) {
            foreach ($this->input->post('NRP') as $NRP) {
                $data = [
                    "mts_id" => $mts_id,
                    "NRP" => $NRP
                ];
                if (!in_array($NRP, array_column($trn_mts_users, "NRP"))) {
                    $data_inserts[] = $data;
                }
            }
        }
        if ($data_inserts) $success = $this->db->insert_batch('trn_mts_user', $data_inserts);
        return $success;
    }

    public function status_change()
    {
        $success = true;
        foreach ($this->input->post('training_users') as $i_tu => $tu_i) {
            $data = [
                'status' => $tu_i
            ];
            echo '<pre>', print_r($data, true);
            $success = $this->db->where('md5(id)', $i_tu)->update('trn_mts_user', $data);
            if (!$success) return $success;
        }
        return $success;
    }
}
