<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

defined('BASEPATH') or exit('No direct script access allowed');

class M_ipa_score extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_current($year, $where = '', $many = true)
    {
        $sql = "
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
            SELECT eis.*, 
                u.FullName, fmp.matrix_point_name mp_name, fmp.node_id mp_id,
                oalp.id oalp_id, oalp.name oalp_name, oalp.parent oalp_parent,
                oal.id oal_id, oal.name oal_name,
                oa.id oa_id, oa.name oa_name
            FROM emp_ipa_score eis
            JOIN (
                SELECT NRP, MAX(tahun) AS max_tahun
                FROM emp_ipa_score
                WHERE tahun <= ?
                GROUP BY NRP
            ) t ON t.NRP = eis.NRP AND t.max_tahun = eis.tahun
            LEFT JOIN rml_sso_la.users u ON u.NRP = eis.NRP
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
        ";

        $query = $this->db->query($sql, [$year]);

        if ($many == false) return $query->row_array();
        return $query->result_array();
    }

    private $table = 'emp_ipa_score';

    public function submit($payload)
    {
        $creates = isset($payload['creates']) && is_array($payload['creates']) ? $payload['creates'] : [];
        $updates = isset($payload['updates']) && is_array($payload['updates']) ? $payload['updates'] : [];
        $deletes = isset($payload['deletes']) && is_array($payload['deletes']) ? $payload['deletes'] : [];

        $payloadYear = isset($payload['year']) ? (int)$payload['year'] : 0;

        $this->db->trans_start();

        // DELETE
        if (!empty($deletes)) {
            $ids = array_values(array_filter(array_map('intval', $deletes)));
            if (!empty($ids)) {
                $this->db->where_in('id', $ids)->delete($this->table);
            }
        }

        // UPDATE
        foreach ($updates as $row) {
            $id = isset($row['id']) ? (int)$row['id'] : 0;
            if ($id <= 0) continue;

            $data = $this->sanitizeRow($row, $payloadYear);
            if (empty($data)) continue;

            $this->db->where('id', $id)->update($this->table, $data);
        }

        // CREATE
        foreach ($creates as $row) {
            $data = $this->sanitizeRow($row, $payloadYear);
            if (empty($data)) continue;

            $this->db->insert($this->table, $data);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Normalisasi & validasi minimal supaya data aman masuk DB
     * - emp_ipa_score: kolom (NRP, tahun, score)
     */
    private function sanitizeRow($row, $payloadYear = 0)
    {
        $NRP = isset($row['NRP']) ? trim((string)$row['NRP']) : '';
        $tahun = (int)$payloadYear; // paksa dari payload

        // score boleh decimal
        $scoreRaw = isset($row['score']) ? trim((string)$row['score']) : '';
        $score = ($scoreRaw === '' ? null : (float)$scoreRaw);

        // minimal rule: NRP & tahun wajib
        if ($NRP === '' || $tahun <= 0) return [];

        // optional: batasi range score
        if ($score !== null) {
            if ($score < 0) $score = 0;
            if ($score > 100) $score = 100;
        }

        return [
            'NRP'   => $NRP,
            'tahun' => $tahun,
            'score' => $score,
        ];
    }
}
