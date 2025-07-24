<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_position extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_comp_position($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $this->db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
        $query = $this->db->query("
            SELECT *, cp.id id FROM comp_position cp
            LEFT JOIN comp_pstn_dict cpd ON cpd.comp_pstn_id = cp.id
            $where
            GROUP BY cp.id
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function add()
    {
        $area_lvl_pstn = $this->m_pstn->get_area_lvl_pstn($this->input->post('hash_area_lvl_pstn_id'), 'md5(oalp.id)', false);
        if ($area_lvl_pstn) {
            $data['area_lvl_pstn_id'] = $area_lvl_pstn['id'];
            $data['name'] = $this->input->post('comp_pstn_name');
            return $this->db->insert('comp_position', $data);
        }
        return false;
    }

    public function edit()
    {
        $data['name'] = $this->input->post('comp_pstn_name');
        $this->db->where('md5(id)', $this->input->post('hash_comp_pstn_id'));
        $success = $this->db->update('comp_position', $data);
        return $success;
    }

    public function delete($hash_id)
    {
        $this->db->where('md5(comp_pstn_id)', $hash_id);
        $success_pstn_target = $this->db->delete('comp_pstn_target');
        $this->db->where('md5(id)', $hash_id);
        $success_pstn = $this->db->delete('comp_position');
        if ($success_pstn_target && $success_pstn) return true;
        return false;
    }

    // public function get_pstn_matrix_point()
    // {
    //     // $query = $this->db->get_where('org_area_lvl', ['pstn_matrix_point' => 1])->row_array();
    //     $query = $this->db->get_where('org_area_lvl_pstn', ['matrix_point' => 1])->result_array();
    //     return $query;
    // }
}
