<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_user extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_user($value = null, $by = 'md5(NRP)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT * FROM rml_sso_la.users
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_area_lvl_pstn_user($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($by) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT oalpu.*,
                oalp.name oalp_name, oalp.id oalp_id,
                oal.name oal_name, oal.id oal_id,
                oa.name oa_name, oa.id oa_id
            FROM org_area_lvl_pstn_user oalpu
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa on oa.id = oalp.area_id
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }
}
