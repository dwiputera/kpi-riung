<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_tna_2026 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    function import($sheet)
    {
        // $this->load->helper(['conversion', 'extract_spreadsheet']);
        // $sheets = extract_spreadsheet("./uploads/imports_admin/LNA 2026 import.xlsx") ?? [];
        // $data['sheets'] = $sheets;
        // $this->session->set_userdata($data);
        // // echo '<pre>', print_r($this->session->userdata['sheets'], true);
        // die;

        $sheets = $this->session->userdata('sheets');
        $rows = array_filter($sheets[$sheet], fn($r_i, $i_r) => $i_r >= 2, ARRAY_FILTER_USE_BOTH);
        $comp_pstns = $this->db->get('comp_position')->result_array();

        $nrps = array_unique(array_column($rows, 2));
        // echo '<pre>', print_r($nrps, true);
        // die;
        foreach ($nrps as $i_nrp => $nrp_i) {
            if ($sheet == 0) {
                if (in_array($nrp_i, array('10122092', '10122091', '10124138', '10125091', '10124094'))) continue;
            }
            $nrp_search = "'$nrp_i', '10$nrp_i'";
            $position = $this->db->query("
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

                SELECT * FROM org_area_lvl_pstn_user oalpu
                LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalpu.area_lvl_pstn_id
                WHERE oalpu.NRP IN ($nrp_search)
            ")->row_array();
            if (!$position) {
                echo '<pre>', print_r('position not found', true);
                echo '<pre>', print_r($nrp_search, true);
            } else {
                $scores = array_filter($rows, fn($r_i, $i_r) => $r_i[2] == $nrp_i, ARRAY_FILTER_USE_BOTH);
                foreach ($scores as $i_score => $score_i) {
                    if ($sheet == 1) {
                        if (in_array($score_i[8], array('Risk Management', 'Corporate Finance', 'Project Cost & Evaluation', 'Operational Management'))) continue;
                    }
                    $comp_pstn = array_filter($comp_pstns, fn($cp_i, $i_cp) => strtolower($cp_i['name']) == strtolower($score_i[8]) && $cp_i['area_lvl_pstn_id'] == $position['mp_id'], ARRAY_FILTER_USE_BOTH);
                    $comp_pstn = array_shift($comp_pstn);

                    if (!$comp_pstn) {
                        echo '<pre>', print_r('comp_pstn not found', true);
                        echo '<pre>', print_r($position, true);
                        echo '<pre>', print_r($score_i, true);
                        die;
                    } else {
                        $data = [
                            'year' => 2026,
                            'NRP' => $position['NRP'],
                            'comp_pstn_id' => $comp_pstn['id'],
                            'score' => $score_i[10],
                        ];
                        echo '<pre>', print_r($data, true);

                        $exist = $this->db->query("
                            SELECT * FROM comp_pstn_score
                            WHERE year = 2026
                            AND comp_pstn_id = $comp_pstn[id]
                            AND NRP = '$position[NRP]'
                        ")->row_array();

                        if ($exist) {
                            echo '<pre>', print_r('exist', true);
                            // $this->db->where('id', $exist['id']);
                            // $success = $this->db->update('comp_pstn_score', $data);
                        } else {
                            echo '<pre>', print_r('not', true);
                            // $success = $this->db->insert('comp_pstn_score', $data);
                        }
                    }
                }
            }
        }
    }
}
