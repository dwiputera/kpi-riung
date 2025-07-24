<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_trn_user extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_trn_user($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT *, trn_user.id id FROM trn_user
            LEFT JOIN rml_sso_la.users sso_usrs ON sso_usrs.NRP = trn_user.NRP
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_user_not_trn($trn_id)
    {
        $query = $this->db->query("
            SELECT * FROM rml_sso_la.users
            WHERE NRP NOT IN (
                SELECT NRP FROM trn_user WHERE trn_id = $trn_id 
            )
        ")->result_array();
        return $query;
    }

    public function add($trn_id)
    {
        $success = false;
        $data_inserts = [];
        $trn_users = $this->m_trn_user->get_trn_user($trn_id, "trn_id");
        $input_nrp = $this->input->post('NRP') ?? [];
        $data_deletes = array_filter($trn_users, fn($tu_i, $i_tu) => !in_array($tu_i['NRP'], $input_nrp), ARRAY_FILTER_USE_BOTH);
        if ($data_deletes) {
            $success = $this->db->where_in('id', array_column($data_deletes, 'id'))->delete('trn_user');
        }
        if ($input_nrp) {
            foreach ($this->input->post('NRP') as $NRP) {
                $data = [
                    "trn_id" => $trn_id,
                    "NRP" => $NRP
                ];
                if (!in_array($NRP, array_column($trn_users, "NRP"))) {
                    $data_inserts[] = $data;
                }
            }
        }
        if ($data_inserts) $success = $this->db->insert_batch('trn_user', $data_inserts);
        return $success;
    }
}
