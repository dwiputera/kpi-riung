<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_employee extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_employee($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT *, 
                users.NRP NRP,
                oalp.id oalp_id, oalp.name oalp_name, oalp.parent oalp_parent,
                oal.id oal_id, oal.name oal_name,
                oa.id oa_id, oa.name oa_name
            FROM rml_sso_la.users
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = users.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            WHERE users.NRP IS NOT NULL
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
