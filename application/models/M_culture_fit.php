<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

defined('BASEPATH') or exit('No direct script access allowed');

class M_culture_fit extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($where = '', $many = true)
    {
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

            SELECT *, cf.id id, cf.NRP NRP, oa.name oa_name, oalp.name oalp_name FROM culture_fit cf
            LEFT JOIN rml_sso_la.users u ON u.NRP = cf.NRP
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
            ORDER BY cf.id
        ");
        if ($many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_current($where = '', $many = true)
    {
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

            SELECT *, cf.id id, cf.NRP NRP, oa.name oa_name, oalp.name oalp_name
            FROM culture_fit cf
            INNER JOIN (
                SELECT NRP, MAX(year) as max_year
                FROM culture_fit
                GROUP BY NRP
            ) tmax ON cf.NRP = tmax.NRP AND cf.year = tmax.max_year
            LEFT JOIN rml_sso_la.users u ON u.NRP = cf.NRP
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
            ORDER BY cf.id
        ");

        if ($many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function submit($payload, $year)
    {
        $updates = $payload['updates'] ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $payload['creates'] ?? [];

        $success = false;

        // 1. Handle UPDATES (existing rows)
        if (!empty($updates)) {
            $ids = array_column($this->db->select('id')->get('culture_fit')->result_array(), 'id');

            $updateData = [];
            foreach ($updates as $row) {
                if (isset($row['id']) && is_numeric($row['id']) && in_array($row['id'], $ids)) {
                    $updateData[] = $row;
                }
            }

            if (!empty($updateData)) {
                $this->db->update_batch('culture_fit', $updateData, 'id');
                $success = true;
            }
        }

        // 2. Handle DELETES
        if (!empty($deletes)) {
            $this->db->where_in('id', $deletes)->delete('culture_fit');
            $success = true;
        }

        // 3. Handle CREATES (new rows)
        if (!empty($creates)) {
            // Remove any rows marked as deleted
            $creates = array_filter($creates, function ($row) use ($deletes) {
                return !(isset($row['id']) && in_array($row['id'], $deletes));
            });

            $createData = [];
            foreach ($creates as $row) {
                if (isset($row['id']) && strpos($row['id'], 'new_') === 0) {
                    unset($row['id']);
                    $row['year'] = $year;
                    $createData[] = $row;
                }
            }

            if (!empty($createData)) {
                $this->db->insert_batch('culture_fit', $createData);
                $success = true;
            }
        }

        return $success;
    }
}
