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
        if ($value) {
            $where = "WHERE $by = " . $this->db->escape($value);
        }

        $query = $this->db->query("
            SELECT * 
            FROM comp_lvl cl
            $where
        ");

        if (($value && !$many) || $many == false) {
            return $query->row_array();
        }

        return $query->result_array();
    }

    public function add()
    {
        $data = [
            'area_lvl_id'       => $this->input->post('area_lvl_id', true),
            'name'              => $this->input->post('name', true),
            'code'              => $this->input->post('code', true),
            'type'              => $this->input->post('type', true),
            'definisi'          => $this->input->post('definisi'),
            'keterangan'        => $this->input->post('keterangan'),

            'dimension_1'       => $this->input->post('dimension_1', true),
            'indicator_1_1_t'   => $this->input->post('indicator_1_1_t'),
            'indicator_1_1_b'   => $this->input->post('indicator_1_1_b'),
            'indicator_1_2_t'   => $this->input->post('indicator_1_2_t'),
            'indicator_1_2_b'   => $this->input->post('indicator_1_2_b'),
            'indicator_1_3_t'   => $this->input->post('indicator_1_3_t'),
            'indicator_1_3_b'   => $this->input->post('indicator_1_3_b'),
            'indicator_1_4_t'   => $this->input->post('indicator_1_4_t'),
            'indicator_1_4_b'   => $this->input->post('indicator_1_4_b'),
            'indicator_1_5_t'   => $this->input->post('indicator_1_5_t'),
            'indicator_1_5_b'   => $this->input->post('indicator_1_5_b'),
            'indicator_1_6_t'   => $this->input->post('indicator_1_6_t'),
            'indicator_1_6_b'   => $this->input->post('indicator_1_6_b'),

            'dimension_2'       => $this->input->post('dimension_2', true),
            'indicator_2_1_t'   => $this->input->post('indicator_2_1_t'),
            'indicator_2_1_b'   => $this->input->post('indicator_2_1_b'),
            'indicator_2_2_t'   => $this->input->post('indicator_2_2_t'),
            'indicator_2_2_b'   => $this->input->post('indicator_2_2_b'),
            'indicator_2_3_t'   => $this->input->post('indicator_2_3_t'),
            'indicator_2_3_b'   => $this->input->post('indicator_2_3_b'),
            'indicator_2_4_t'   => $this->input->post('indicator_2_4_t'),
            'indicator_2_4_b'   => $this->input->post('indicator_2_4_b'),
            'indicator_2_5_t'   => $this->input->post('indicator_2_5_t'),
            'indicator_2_5_b'   => $this->input->post('indicator_2_5_b'),
            'indicator_2_6_t'   => $this->input->post('indicator_2_6_t'),
            'indicator_2_6_b'   => $this->input->post('indicator_2_6_b'),

            'dimension_3'       => $this->input->post('dimension_3', true),
            'indicator_3_1_t'   => $this->input->post('indicator_3_1_t'),
            'indicator_3_1_b'   => $this->input->post('indicator_3_1_b'),
            'indicator_3_2_t'   => $this->input->post('indicator_3_2_t'),
            'indicator_3_2_b'   => $this->input->post('indicator_3_2_b'),
            'indicator_3_3_t'   => $this->input->post('indicator_3_3_t'),
            'indicator_3_3_b'   => $this->input->post('indicator_3_3_b'),
            'indicator_3_4_t'   => $this->input->post('indicator_3_4_t'),
            'indicator_3_4_b'   => $this->input->post('indicator_3_4_b'),
            'indicator_3_5_t'   => $this->input->post('indicator_3_5_t'),
            'indicator_3_5_b'   => $this->input->post('indicator_3_5_b'),
            'indicator_3_6_t'   => $this->input->post('indicator_3_6_t'),
            'indicator_3_6_b'   => $this->input->post('indicator_3_6_b'),
        ];

        return $this->db->insert('comp_lvl', $data);
    }

    public function edit()
    {
        $data = [
            'area_lvl_id'       => $this->input->post('area_lvl_id', true),
            'name'              => $this->input->post('name', true),
            'code'              => $this->input->post('code', true),
            'type'              => $this->input->post('type', true),
            'definisi'          => $this->input->post('definisi'),
            'keterangan'        => $this->input->post('keterangan'),

            'dimension_1'       => $this->input->post('dimension_1', true),
            'indicator_1_1_t'   => $this->input->post('indicator_1_1_t'),
            'indicator_1_1_b'   => $this->input->post('indicator_1_1_b'),
            'indicator_1_2_t'   => $this->input->post('indicator_1_2_t'),
            'indicator_1_2_b'   => $this->input->post('indicator_1_2_b'),
            'indicator_1_3_t'   => $this->input->post('indicator_1_3_t'),
            'indicator_1_3_b'   => $this->input->post('indicator_1_3_b'),
            'indicator_1_4_t'   => $this->input->post('indicator_1_4_t'),
            'indicator_1_4_b'   => $this->input->post('indicator_1_4_b'),
            'indicator_1_5_t'   => $this->input->post('indicator_1_5_t'),
            'indicator_1_5_b'   => $this->input->post('indicator_1_5_b'),
            'indicator_1_6_t'   => $this->input->post('indicator_1_6_t'),
            'indicator_1_6_b'   => $this->input->post('indicator_1_6_b'),

            'dimension_2'       => $this->input->post('dimension_2', true),
            'indicator_2_1_t'   => $this->input->post('indicator_2_1_t'),
            'indicator_2_1_b'   => $this->input->post('indicator_2_1_b'),
            'indicator_2_2_t'   => $this->input->post('indicator_2_2_t'),
            'indicator_2_2_b'   => $this->input->post('indicator_2_2_b'),
            'indicator_2_3_t'   => $this->input->post('indicator_2_3_t'),
            'indicator_2_3_b'   => $this->input->post('indicator_2_3_b'),
            'indicator_2_4_t'   => $this->input->post('indicator_2_4_t'),
            'indicator_2_4_b'   => $this->input->post('indicator_2_4_b'),
            'indicator_2_5_t'   => $this->input->post('indicator_2_5_t'),
            'indicator_2_5_b'   => $this->input->post('indicator_2_5_b'),
            'indicator_2_6_t'   => $this->input->post('indicator_2_6_t'),
            'indicator_2_6_b'   => $this->input->post('indicator_2_6_b'),

            'dimension_3'       => $this->input->post('dimension_3', true),
            'indicator_3_1_t'   => $this->input->post('indicator_3_1_t'),
            'indicator_3_1_b'   => $this->input->post('indicator_3_1_b'),
            'indicator_3_2_t'   => $this->input->post('indicator_3_2_t'),
            'indicator_3_2_b'   => $this->input->post('indicator_3_2_b'),
            'indicator_3_3_t'   => $this->input->post('indicator_3_3_t'),
            'indicator_3_3_b'   => $this->input->post('indicator_3_3_b'),
            'indicator_3_4_t'   => $this->input->post('indicator_3_4_t'),
            'indicator_3_4_b'   => $this->input->post('indicator_3_4_b'),
            'indicator_3_5_t'   => $this->input->post('indicator_3_5_t'),
            'indicator_3_5_b'   => $this->input->post('indicator_3_5_b'),
            'indicator_3_6_t'   => $this->input->post('indicator_3_6_t'),
            'indicator_3_6_b'   => $this->input->post('indicator_3_6_b'),
        ];

        $this->db->where('md5(id)', $this->input->post('hash_comp_lvl_id'));
        return $this->db->update('comp_lvl', $data);
    }

    public function delete($hash_id)
    {
        $this->db->trans_start();

        $this->db->where('md5(comp_lvl_id)', $hash_id);
        $this->db->delete('comp_lvl_target');

        $this->db->where('md5(id)', $hash_id);
        $this->db->delete('comp_lvl');

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
