<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_rtc extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    function get($where = '', $many = true)
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
                    current_id AS mp_id,
                    matrix_point_name
                FROM (
                    SELECT 
                        start_id, current_id,
                        matrix_point_name,
                        ROW_NUMBER() OVER (PARTITION BY start_id ORDER BY depth ASC) AS rn
                    FROM matrix_point_resolve
                    WHERE matrix_point_name IS NOT NULL
                ) ranked
                WHERE rn = 1
            )
            SELECT * FROM rtc
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = rtc.oalp_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
        ");
        if ($many == false) return $query->row_array();
        return $query->result_array();
    }

    public function get_current($where = '', $many = true)
    {
        $query = $this->db->query("
            SELECT * FROM org_area_lvl_pstn oalp
            $where
        ");
        if ($many == false) return $query->row_array();
        return $query->result_array();
    }

    private $table = 'rtc'; // contoh: schema.table

    /**
     * Submit RTC (update all rows for year1 & year2)
     * Strategy: per (oalp_id, year) -> delete then insert selected NRP(s)
     */
    public function submit(array $payload)
    {
        if (empty($payload)) return false;

        $year1 = isset($payload['year1']) ? (int)$payload['year1'] : (int)date('Y') + 1;
        $year2 = isset($payload['year2']) ? (int)$payload['year2'] : (int)date('Y') + 2;

        $updates = isset($payload['updates']) && is_array($payload['updates']) ? $payload['updates'] : [];
        if (!$updates) return true; // nothing to do

        $colY1 = 'year_' . $year1;
        $colY2 = 'year_' . $year2;

        // user audit (kalau kamu punya session)
        $userNrp = $this->session->userdata('NRP') ?: null;

        $this->db->trans_begin();

        try {
            foreach ($updates as $row) {
                if (!isset($row['id'])) continue;

                $oalp_id = (int)$row['id'];
                if ($oalp_id <= 0) continue;

                // ambil list nrp untuk masing2 year
                $nrps1 = isset($row[$colY1]) && is_array($row[$colY1]) ? $row[$colY1] : [];
                $nrps2 = isset($row[$colY2]) && is_array($row[$colY2]) ? $row[$colY2] : [];

                // normalisasi (string, unik, buang kosong)
                $nrps1 = array_values(array_unique(array_filter(array_map('strval', $nrps1))));
                $nrps2 = array_values(array_unique(array_filter(array_map('strval', $nrps2))));

                // ===== YEAR 1: delete then insert =====
                $this->db->where('year', $year1)->where('oalp_id', $oalp_id)->delete($this->table);

                if (!empty($nrps1)) {
                    $insert1 = [];
                    foreach ($nrps1 as $nrp) {
                        $insert1[] = [
                            'year'    => $year1,
                            'oalp_id' => $oalp_id,
                            'NRP'     => $nrp,

                            // optional audit fields (hapus kalau kolom tidak ada)
                            // 'created_by' => $userNrp,
                            // 'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    $this->db->insert_batch($this->table, $insert1);
                }

                // ===== YEAR 2: delete then insert =====
                $this->db->where('year', $year2)->where('oalp_id', $oalp_id)->delete($this->table);

                if (!empty($nrps2)) {
                    $insert2 = [];
                    foreach ($nrps2 as $nrp) {
                        $insert2[] = [
                            'year'    => $year2,
                            'oalp_id' => $oalp_id,
                            'NRP'     => $nrp,

                            // optional audit fields (hapus kalau kolom tidak ada)
                            // 'created_by' => $userNrp,
                            // 'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    $this->db->insert_batch($this->table, $insert2);
                }
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }

            $this->db->trans_commit();
            return true;
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', '[M_rtc::submit] ' . $e->getMessage());
            return false;
        }
    }
}
