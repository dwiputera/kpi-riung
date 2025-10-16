<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

defined('BASEPATH') or exit('No direct script access allowed');

class M_health_status extends CI_Model
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

            SELECT *, hsu.id id, hsu.NRP NRP, hsu.year year,
                hs.name hs_name,
                oalp.id oalp_id, oalp.name oalp_name, oalp.parent oalp_parent,
                oal.id oal_id, oal.name oal_name,
                oa.id oa_id, oa.name oa_name
            FROM health_status_user hsu
            LEFT JOIN health_status hs ON hs.id = hsu.status_id
            LEFT JOIN rml_sso_la.users u ON u.NRP = hsu.NRP
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
            ORDER BY hsu.id
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

            SELECT *, hsu.id id, hsu.NRP NRP, hsu.year year,
                hs.name hs_name,
                oalp.id oalp_id, oalp.name oalp_name, oalp.parent oalp_parent,
                oal.id oal_id, oal.name oal_name,
                oa.id oa_id, oa.name oa_name
            FROM health_status_user hsu
            INNER JOIN (
                SELECT NRP, MAX(year) as max_year
                FROM health_status_user
                GROUP BY NRP
            ) tmax ON hsu.NRP = tmax.NRP AND hsu.year = tmax.max_year
            LEFT JOIN health_status hs ON hs.id = hsu.status_id
            LEFT JOIN rml_sso_la.users u ON u.NRP = hsu.NRP
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
            ORDER BY hsu.id
        ");

        if ($many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    function emptyStringToNull($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'emptyStringToNull'], $data);
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->emptyStringToNull($v);
            }
            return $data;
        }
        return $data === '' ? null : $data;
    }



    public function submit($payload, $year)
    {
        $updates = $this->emptyStringToNull($payload['updates']) ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $this->emptyStringToNull($payload['creates']) ?? [];

        $success = false;

        // 1. Handle UPDATES (existing rows)
        if (!empty($updates)) {
            $ids = array_column($this->db->select('id')->get('health_status_user')->result_array(), 'id');

            $updateData = [];
            foreach ($updates as $row) {
                if (isset($row['id']) && is_numeric($row['id']) && in_array($row['id'], $ids)) {
                    $updateData[] = $row;
                }
            }

            if (!empty($updateData)) {
                $this->db->update_batch('health_status_user', $updateData, 'id');
                $success = true;
            }
        }

        // 2. Handle DELETES
        if (!empty($deletes)) {
            $this->db->where_in('id', $deletes)->delete('health_status_user');
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
                $this->db->insert_batch('health_status_user', $createData);
                $success = true;
            }
        }

        return $success;
    }
}
