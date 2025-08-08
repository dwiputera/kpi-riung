<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_lvl_assess extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
    }

    public function get_comp_lvl_emp_assess($value = null, $by = 'md5(e.NRP)', $many = true)
    {
        $where = '';
        if ($value) {
            $where = "WHERE $by = '$value'";
        }

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

            SELECT 
                e.NRP,
                e.FullName,
                cla.id AS comp_lvl_assess_id,
                cla.method_id,
                cla.tahun,
                cla.vendor,
                cla.recommendation,
                cla.score assess_score,
                clam.name method,
                
                oalp.id AS oalp_id, oalp.name AS oalp_name, oalp.parent AS oalp_parent,
                oal.id AS oal_id, oal.name AS oal_name,
                oa.id AS oa_id, oa.name AS oa_name,
                fmp.matrix_point_name,

                COALESCE(
                    JSON_ARRAYAGG(
                        IF(clas.comp_lvl_id IS NOT NULL,
                            JSON_OBJECT(
                                'score', clas.score,
                                'comp_lvl_id', clas.comp_lvl_id
                            ),
                            NULL
                        )
                    ),
                    JSON_ARRAY()
                ) AS score,

                ROUND(AVG(clas.score), 2) AS avg_score,

                -- Positional scores as array (tetap per NRP)
                (
                    SELECT COALESCE(JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'tahun', eis.tahun,
                            'score', eis.score
                        )
                    ), JSON_ARRAY())
                    FROM emp_ipa_score eis
                    WHERE eis.NRP = e.NRP
                ) AS pstn_scores,

                -- Average of positional scores
                (
                    SELECT ROUND(AVG(eis.score), 2)
                    FROM emp_ipa_score eis
                    WHERE eis.NRP = e.NRP
                ) AS avg_ipa_score

            FROM rml_sso_la.users e
            LEFT JOIN comp_lvl_assess cla ON cla.NRP = e.NRP
            LEFT JOIN comp_lvl_assess_method clam ON clam.id = cla.method_id
            LEFT JOIN comp_lvl_assess_score clas ON clas.comp_lvl_assess_id = cla.id
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = e.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
            $where
            GROUP BY e.NRP, cla.id
            ORDER BY e.NRP, cla.id
        ");

        if (($value && !$many) || $many === false) {
            $row = $query->row_array();
            if (!$row) return false;
            $row['score'] = !empty($row['score']) ? json_decode($row['score'], true) : [];
            $row['pstn_scores'] = !empty($row['pstn_scores']) ? json_decode($row['pstn_scores'], true) : [];
            $row['avg_score'] = $row['avg_score'] !== null ? floatval($row['avg_score']) : null;
            $row['avg_ipa_score'] = $row['avg_ipa_score'] !== null ? floatval($row['avg_ipa_score']) : null;
            return $row;
        } else {
            $rows = $query->result_array();
            foreach ($rows as &$row) {
                $row['score'] = !empty($row['score']) ? json_decode($row['score'], true) : [];
                $row['pstn_scores'] = !empty($row['pstn_scores']) ? json_decode($row['pstn_scores'], true) : [];
                $row['avg_score'] = $row['avg_score'] !== null ? floatval($row['avg_score']) : null;
                $row['avg_ipa_score'] = $row['avg_ipa_score'] !== null ? floatval($row['avg_ipa_score']) : null;
            }
            return $rows;
        }
    }

    function submit($NRP_hash)
    {
        $submitted_data = json_decode($this->input->post('target_json'), true);
        $cla = $this->db->get_where('comp_lvl_assess', array('md5(NRP)' => $NRP_hash, 'method_id' => $this->input->post('method_id')))->row_array();
        $cla_data = [
            'NRP' => $this->input->post('NRP'),
            'score' => $submitted_data['assess_score'],
            'method_id' => $this->input->post('method_id'),
            'recommendation' => $submitted_data['recommendation'],
            'tahun' => $submitted_data['tahun'],
            'vendor' => $submitted_data['vendor'],
        ];

        if (!$cla) {
            $success = $this->db->insert('comp_lvl_assess', $cla_data);
            $cla_id = $this->db->insert_id();
        } else {
            $this->db->where('id', $cla['id']);
            $success = $this->db->update('comp_lvl_assess', $cla_data);
            $cla_id = $cla['id'];
        }

        if ($success) {
            $comp_lvls = $this->db->get('comp_lvl')->result_array();
            foreach ($comp_lvls as $i_cl => $cl_i) {
                $clas_data = [
                    'comp_lvl_assess_id' => $cla_id,
                    'comp_lvl_id' => $cl_i['id'],
                    'score' => $submitted_data['score'][$cl_i['id']],
                ];
                $clas = $this->db->get_where('comp_lvl_assess_score', ['comp_lvl_assess_id' => $cla_id, 'comp_lvl_id' => $cl_i['id']])->row_array();
                echo '<pre>', print_r($clas_data, true);
                if (!$clas) {
                    $success = $this->db->insert('comp_lvl_assess_score', $clas_data);
                    echo '<pre>', print_r("insert", true);
                } else {
                    $this->db->where('comp_lvl_assess_id', $cla_id);
                    $this->db->where('comp_lvl_id', $cl_i['id']);
                    $success = $this->db->update('comp_lvl_assess_score', $clas_data);
                    echo '<pre>', print_r("update", true);
                }
                echo '<pre>', print_r($success, true);
            }
        }
        return $success;
    }
}
