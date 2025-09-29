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
        if ($value) $where = "AND $by = '$value'";
        if ($value == 'IS NULL' || $value == 'IS NOT NULL') $where = 'AND ' . $by . ' ' . $value;
        $query = $this->db->query("
            WITH RECURSIVE matrix_point_resolve AS (
                SELECT 
                    oalp.id AS start_id,
                    oalp.id AS current_id,
                    oalp.parent,
                    oalp.matrix_point,
                    oalp.name,
                    oalp.type,
                    CASE
                        WHEN oalp.type = 'matrix_point' THEN oalp.name
                        ELSE NULL
                    END AS matrix_point_name,
                    0 AS depth
                FROM org_area_lvl_pstn oalp

                UNION ALL

                SELECT 
                    m.start_id,
                    o.id,
                    o.parent,
                    o.matrix_point,
                    o.name,
                    o.type,
                    CASE
                        WHEN o.type = 'matrix_point' THEN o.name
                        ELSE m.matrix_point_name
                    END AS matrix_point_name,
                    m.depth + 1
                FROM matrix_point_resolve m
                JOIN org_area_lvl_pstn o 
                    ON o.id = m.parent OR o.id = m.matrix_point
                WHERE m.matrix_point_name IS NULL
            ),

            final_matrix_point AS (
                SELECT 
                    start_id AS node_id,
                    matrix_point_name
                FROM (
                    SELECT 
                        start_id, 
                        matrix_point_name,
                        ROW_NUMBER() OVER (PARTITION BY start_id ORDER BY depth ASC) AS rn
                    FROM matrix_point_resolve
                    WHERE matrix_point_name IS NOT NULL
                ) ranked
                WHERE rn = 1
            )

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
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
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
