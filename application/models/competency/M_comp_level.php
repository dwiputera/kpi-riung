<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_level extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_comp_level($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT * FROM comp_lvl cl
            $where
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
        $data['name'] = $this->input->post('comp_lvl_name');
        return $this->db->insert('comp_lvl', $data);
    }

    public function edit()
    {
        $data['name'] = $this->input->post('comp_lvl_name');
        $this->db->where('md5(id)', $this->input->post('hash_comp_lvl_id'));
        $success = $this->db->update('comp_lvl', $data);
        return $success;
    }

    public function delete($hash_id)
    {
        $this->db->where('md5(comp_lvl_id)', $hash_id);
        $success_lvl_target = $this->db->delete('comp_lvl_target');
        $this->db->where('md5(id)', $hash_id);
        $success_lvl = $this->db->delete('comp_lvl');
        if ($success_lvl_target && $success_lvl) return true;
        return false;
    }
}
