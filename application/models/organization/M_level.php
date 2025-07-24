<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_level extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_area_lvl($value = null, $by = 'md5(oalp.id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
        SELECT 
            oal.*,
            oal.id id, oal.name name
        FROM org_area_lvl oal
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
        $data['area_id'] = $this->m_area->get_area($this->input->post('area'))['id'];
        $data['parent'] = $this->get_area_lvl($this->input->post('parent_lvl'))['id'];
        $data['name'] = $this->input->post('level_name');
        $success = $this->db->insert('org_area_lvl', $data);
        return $success;
    }

    public function delete($hash_id)
    {
        $this->db->where('md5(id)', $hash_id);
        $success = $this->db->delete('org_area_lvl');
        return $success;
    }

    public function get_area_lvl_pstn($hash_id)
    {
        $query = $this->db->query("
            SELECT *, 
                oalp.id id, oalp.name name, oalp.parent oalp_parent,
                oal.id oal_id, oal.name oal_name, oal.parent oal_parent,
                oa.id oa_id, oa.name oa_name
            FROM org_area_lvl_pstn oalp
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oal.area_id
        ")->result_array();
        if ($hash_id) {
            $query = array_filter($query, fn($q_i, $i_q) => md5($q_i['oal_id']) == $hash_id, ARRAY_FILTER_USE_BOTH);
            if ($query) {
                $query = array_values($query);
                if (isset($query[0])) $query = $query[0];
            }
        }
        return $query;
    }

    function get_subordinates($hash_lvl_id)
    {
        $subordinates = $this->db->query("
            WITH RECURSIVE levels AS (
                SELECT *
                FROM org_area_lvl
                WHERE md5(id) = '$hash_lvl_id'

                UNION ALL

                SELECT o.*
                FROM org_area_lvl o
                INNER JOIN levels t ON o.parent = t.id
            )
            #SELECT * FROM levels;
            SELECT
                oal.*,
                oal.id AS oal_id,
                oal.name AS oal_name,
                oa.name AS oa_name,
                oal_p.name AS oal_p_name,
                oa_p.name AS oa_p_name
            FROM levels oal
        ")->result_array();
        return $subordinates;
    }
}
