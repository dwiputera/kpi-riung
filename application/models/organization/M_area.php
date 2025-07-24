<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_area extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_area($hash_id = null)
    {
        $query = $this->db->query("
            SELECT * FROM org_area oa
        ")->result_array();
        if ($hash_id) {
            $query = array_filter($query, fn($q_i, $i_q) => md5($q_i['id']) == $hash_id, ARRAY_FILTER_USE_BOTH);
            if ($query) {
                $query = array_values($query);
                if (isset($query[0])) $query = $query[0];
            }
        }
        return $query;
    }

    public function add()
    {
        $data['name'] = $this->input->post('area_name');
        $success = $this->db->insert('org_area', $data);
        return $success;
    }

    public function delete($hash_id)
    {
        $this->db->where('md5(id)', $hash_id);
        $success = $this->db->delete('org_area');
        return $success;
    }

    public function get_area_lvl($hash_id = null)
    {
        $query = $this->db->query("
        SELECT 
            oal.*,
            oal.id AS oal_id,
            oal.name AS oal_name,
            oa.name AS oa_name,
            oal_p.id AS oal_p_id,
            oal_p.name AS oal_p_name,
            oa_p.name AS oa_p_name
        FROM org_area_lvl oal
        LEFT JOIN org_area oa ON oa.id = oal.area_id
        LEFT JOIN org_area_lvl oal_p ON oal_p.id = oal.parent
        LEFT JOIN org_area oa_p ON oa_p.id = oal_p.area_id
        ORDER BY oa.id, oal_p.parent;
        ")->result_array();
        if ($hash_id) {
            $query = array_filter($query, fn($q_i, $i_q) => md5($q_i['area_id']) == $hash_id, ARRAY_FILTER_USE_BOTH);
            if ($query) {
                $query = array_values($query);
                if (isset($query[0])) $query = $query[0];
            }
        }
        return $query;
    }
}
