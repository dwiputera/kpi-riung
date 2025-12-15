<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_talent extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
    }

    public function get_candidate($position, $method_id)
    {
        if ($position) {
            $this->load->model('competency/m_comp_position', 'm_c_pstn');
            $correlation_matrix = array_column($this->m_c_pstn->get_correlation_matrix(), null, 'id');
            $candidate_level = $position['oal_id'] + 1;
            $candidate_level = $this->db->where('id', $candidate_level)->or_where('equals', $candidate_level)->get('org_area_lvl')->result_array();
            $candidate_level_ids = array_column($candidate_level, 'id'); // ambil semua value kolom 'id'
            $candidate_level_ids_string = implode(',', $candidate_level_ids);    // gabung jadi string

            $this->load->model('m_hav');
            $this->load->model('organization/m_position');
            $this->load->model('m_tour_of_duty', 'm_tod');

            $employees = $this->db->query("
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
                SELECT 
                    u.NRP, u.FullName,
                    a.assess_score, a.recommendation,
                    FORMAT(p.avg_ipa_score, 2) avg_ipa_score, FORMAT(p.ipa_score, 2) ipa_score,
                    TIMESTAMPDIFF(YEAR, u.BirthDate, CURDATE()) age,
                    oal.name level_name, oal.id oal_id,
                    oalp.id oalp_id,
                    clam.name clam_name,
                    cf.nilai_behaviour culture_fit,
                    fmp.matrix_point_name AS mp_name,
                    fmp.mp_id,
                    hsu.status_id hsu_status_id,
                    hs.name hs_name,
                    null tour_of_duty
                FROM rml_sso_la.users u
                LEFT JOIN (
                    SELECT * FROM (
                        SELECT 
                            cla.NRP,
                            MAX(cla.score) AS assess_score,
                            MAX(cla.recommendation) AS recommendation,
                            cla.tahun,
                            ROW_NUMBER() OVER (
                                PARTITION BY cla.NRP 
                                ORDER BY cla.tahun DESC, cla.method_id
                            ) AS rn,
                            method_id
                        FROM comp_lvl_assess cla 
                        WHERE cla.method_id IN ($method_id)
                        #AND cla.tahun <= 2024
                        GROUP BY cla.NRP, cla.tahun, cla.method_id
                    ) AS ranked
                    WHERE ranked.rn = 1
                ) a ON a.NRP = u.NRP
                LEFT JOIN (
                    SELECT 
                        NRP,
                        AVG(score) avg_ipa_score,
                        SUM(score) ipa_score
                    FROM emp_ipa_score
                    WHERE tahun BETWEEN YEAR(CURDATE()) - 3 AND YEAR(CURDATE()) - 1
                    GROUP BY NRP
                ) p ON p.NRP = u.NRP
                LEFT JOIN (
                    SELECT NRP, nilai_behaviour, year
                    FROM (
                        SELECT 
                            cf.NRP, cf.nilai_behaviour, cf.year,
                            ROW_NUMBER() OVER (
                                PARTITION BY cf.NRP ORDER BY cf.year DESC, cf.id DESC
                            ) AS rn
                        FROM culture_fit_user cf
                    ) x
                    WHERE x.rn = 1
                ) cf ON cf.NRP = u.NRP
                LEFT JOIN (
                    SELECT NRP, status_id, year
                    FROM (
                        SELECT 
                            hsu.NRP, hsu.status_id, hsu.year,
                            ROW_NUMBER() OVER (
                                PARTITION BY hsu.NRP ORDER BY hsu.year DESC, hsu.id DESC
                            ) AS rn
                        FROM health_status_user hsu
                    ) x
                    WHERE x.rn = 1
                ) hsu ON hsu.NRP = u.NRP
                LEFT JOIN health_status hs ON hs.id = hsu.status_id
                LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
                LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
                LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
                LEFT JOIN org_area oa ON oa.id = oalp.area_id
                LEFT JOIN comp_lvl_assess_method clam ON clam.id = a.method_id
                LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
                WHERE oal.id IN($candidate_level_ids_string);
            ")->result_array();

            foreach ($employees as &$e) {
                $e['correlation_matrix'] = $correlation_matrix[$e['mp_id']]['correlations'][$position['mp_id']];
                $tour_of_duty = $this->m_tod->get_tod_with_mp("WHERE NRP = '$e[NRP]' ORDER BY date DESC");
                $durations = $this->m_tod->calculate_duration_per_matrix_point($tour_of_duty);
                $e['tour_of_duty'] = $this->get_score_tour_of_duty($durations);
            }

            $this->load->model('competency/M_comp_level_target', 'm_c_l_t');
            $comp_lvl_target = $this->m_c_l_t->get_comp_level_target($position['id'], 'area_lvl_pstn_id');
            $comp_lvl_target = array_filter($comp_lvl_target, fn($clt_i, $i_clt) => $clt_i['target'], ARRAY_FILTER_USE_BOTH);

            $employees = $this->m_hav->calculate_hav_status($employees);
            $employees = $this->m_tal->calculate_candidate_scores($employees, $position, $comp_lvl_target, $method_id);
            $candidate_nrps = array_column($employees, 'NRP'); // ambil semua value kolom 'id'
            $positions = $this->m_position->get_area_lvl_pstn_user($candidate_nrps, 'u.NRP');
            $map = array_column($positions, null, 'NRP');

            foreach ($employees as &$e) {
                $e += $map[$e['NRP']] ?? [];
            }
            return $employees;
        }
        return [];
    }

    function calculate_candidate_scores($employees, $position, $comp_lvl_target, $method_id)
    {
        $this->load->model('competency/m_comp_position_score', 'm_c_p_s');
        $percentage = $this->get_percentage($position['oal_id']);
        foreach ($employees as &$emp) {

            $emp['kompetensi_teknis'] = null;
            $emp['kompetensi_teknis_percentage'] = null;
            $comp_pstn_score = $this->m_c_p_s->get_cp_score($emp['NRP'], 'cps.NRP');
            if ($comp_pstn_score) {
                $comp_pstn = $this->db->get_where('comp_position', array('area_lvl_pstn_id' => $emp['mp_id']))->result_array();
                $comp_pstn_ids = array_column($comp_pstn, 'id');
                $comp_pstn_id_string = implode("','", $comp_pstn_ids);
                $comp_pstn_target = $this->db->query("
                    SELECT * FROM comp_pstn_target
                    WHERE comp_pstn_id IN ('$comp_pstn_id_string')
                    AND area_lvl_pstn_id = $emp[oalp_id]
                    AND target != 0
                ")->result_array();
                $comp_pstn_target_sum = array_sum(array_column($comp_pstn_target, 'target'));
                $comp_pstn_target_comp_pstn_ids = array_column($comp_pstn_target, 'comp_pstn_id');
                $comp_pstn_score_filter = array_filter($comp_pstn_score, fn($cps_i, $i_cps) => in_array($cps_i['comp_pstn_id'], $comp_pstn_target_comp_pstn_ids), ARRAY_FILTER_USE_BOTH);
                $comp_pstn_score_sum = array_sum(array_column($comp_pstn_score_filter, 'score'));
                if ($comp_pstn_target_sum) {
                    $emp['kompetensi_teknis'] = number_format($comp_pstn_score_sum * 100 / $comp_pstn_target_sum, 2);
                    $emp['kompetensi_teknis_percentage'] = $emp['kompetensi_teknis'] . '%';
                } else {
                    $emp['kompetensi_teknis_percentage'] = "no target";
                }
            }

            $emp['job_fit_score'] = null;
            $comp_lvl_assess = $this->db->get_where('comp_lvl_assess', array('NRP' => $emp['NRP'], 'method_id' => $method_id))->row_array();
            if ($comp_lvl_assess) {
                $comp_lvl_assess_score = $this->db->get_where('comp_lvl_assess_score', array('comp_lvl_assess_id' => $comp_lvl_assess['id']))->result_array();
                $comp_lvl_target_ids = array_column($comp_lvl_target, 'comp_lvl_id');
                $comp_lvl_assess_score = array_filter($comp_lvl_assess_score, fn($clas_i, $i_clas) => in_array($clas_i['comp_lvl_id'], $comp_lvl_target_ids), ARRAY_FILTER_USE_BOTH);
                $job_fit_score = array_sum(array_column($comp_lvl_assess_score, 'score')) * 100 / array_sum(array_column($comp_lvl_target, 'target'));
                $emp['job_fit_score'] = number_format($job_fit_score, 2);
            }

            $emp['score_kompetensi_teknis'] = $this->get_score_kompetensi_teknis($emp['kompetensi_teknis']);
            $emp['score_job_fit_score'] = $this->get_score_job_fit_score($emp['job_fit_score']);
            $emp['score_avg_ipa_score'] = $this->get_score_avg_ipa_score($emp['avg_ipa_score']);
            $emp['score_tour_of_duty'] = $emp['tour_of_duty'];
            $emp['score_culture_fit'] = $this->get_score_culture_fit($emp['culture_fit']);
            $emp['score_age'] = $this->get_score_age($emp['age']);
            $emp['score_health_status'] = $this->get_score_health_status($emp['hsu_status_id']);
            $emp['score_kategori_hav_mapping'] = $this->get_score_kategori_hav_mapping($emp['status']);
            $emp['score_assess_score'] = $this->get_score_assess_score($emp['assess_score']);
            $emp['score_correlation_matrix'] = $this->get_score_correlation_matrix($emp['correlation_matrix']);

            $emp['score_nxb_kompetensi_teknis'] = number_format($emp['score_kompetensi_teknis'] * $percentage['kompetensi_teknis'] / 100, 2);
            $emp['score_nxb_job_fit_score'] = number_format($emp['score_job_fit_score'] * $percentage['job_fit_score'] / 100, 2);
            $emp['score_nxb_avg_ipa_score'] = number_format($emp['score_avg_ipa_score'] * $percentage['avg_ipa_score'] / 100, 2);
            $emp['score_nxb_tour_of_duty'] = number_format($emp['score_tour_of_duty'] * $percentage['tour_of_duty'] / 100, 2);
            $emp['score_nxb_culture_fit'] = number_format($emp['score_culture_fit'] * $percentage['culture_fit'] / 100, 2);
            $emp['score_nxb_age'] = number_format($emp['score_age'] * $percentage['age'] / 100, 2);
            $emp['score_nxb_health_status'] = number_format($emp['score_health_status'] * $percentage['health_status'] / 100, 2);
            $emp['score_nxb_kategori_hav_mapping'] = number_format($emp['score_kategori_hav_mapping'] * $percentage['kategori_hav_mapping'] / 100, 2);
            $emp['score_nxb_assess_score'] = number_format($emp['score_assess_score'] * $percentage['assess_score'] / 100, 2);
            $emp['score_nxb_correlation_matrix'] = number_format($emp['score_correlation_matrix'] * $percentage['correlation_matrix'] / 100, 2);

            $score_to_sum = array('kompetensi_teknis', 'job_fit_score', 'avg_ipa_score', 'tour_of_duty', 'culture_fit', 'age', 'health_status', 'kategori_hav_mapping', 'assess_score', 'correlation_matrix');

            $emp['total_score'] = 0;
            foreach ($score_to_sum as $sts) {
                $emp['total_score'] += $emp["score_nxb_$sts"];
            }
        }
        return $employees;
    }

    function get_percentage($level_id)
    {
        $criteria = array('kompetensi_teknis', 'job_fit_score', 'avg_ipa_score', 'tour_of_duty', 'culture_fit', 'age', 'health_status', 'kategori_hav_mapping', 'assess_score', 'correlation_matrix');
        $percentage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        if (in_array($level_id, array(2))) {
            $percentage = [8, 32, 10, 10, 5, 5, 5, 10, 10, 5];
        } elseif (in_array($level_id, array(3, 6, 7))) {
            $percentage = [20, 20, 10, 10, 5, 5, 5, 10, 10, 5];
        } elseif (in_array($level_id, array(4, 8))) {
            $percentage = [24, 16, 10, 10, 5, 5, 5, 10, 10, 5];
        } elseif (in_array($level_id, array(9))) {
            $percentage = [32, 8, 10, 10, 5, 5, 5, 10, 10, 5];
        }

        foreach ($criteria as $i_crit => $crit_i) {
            $data["$crit_i"] = $percentage[$i_crit];
        }

        return $data;
    }

    function get_score_kompetensi_teknis($cp_percentage)
    {
        if ($cp_percentage > 90) return 5;
        if ($cp_percentage > 80) return 4;
        if ($cp_percentage > 70) return 3;
        if ($cp_percentage > 60) return 2;
        if ($cp_percentage > 50) return 1;
        return null;
    }

    function get_score_job_fit_score($job_fit_score)
    {
        if ($job_fit_score > 90) return 5;
        if ($job_fit_score > 80) return 4;
        if ($job_fit_score > 70) return 3;
        if ($job_fit_score > 60) return 2;
        if ($job_fit_score > 50) return 1;
        return null;
    }

    function get_score_avg_ipa_score($avg_ipa_score)
    {
        if ($avg_ipa_score > 4.55) return 5;
        if ($avg_ipa_score > 4) return 4;
        if ($avg_ipa_score > 3.56) return 3;
        if ($avg_ipa_score > 3) return 2;
        if ($avg_ipa_score > 2.5) return 1;
        return null;
    }

    function get_score_tour_of_duty($durations)
    {
        if (!is_array($durations)) return null;
        $core_function_ids = [8, 9, 45];
        if (!$durations) return 0;
        $tod_core = array_filter($durations, fn($dur_i) => in_array($dur_i['matrix_point_id'], $core_function_ids));
        if (!$tod_core) return 1;
        $tod_not_core = array_filter($durations, fn($dur_i) => !in_array($dur_i['matrix_point_id'], $core_function_ids));
        if (!$tod_not_core) return 1;
        $tod_not_core_year_more = array_filter($tod_not_core, fn($todnc_i) => $todnc_i['year_count'] >= 1);
        if (count($tod_not_core_year_more) >= 4) return 5;
        if (count($tod_not_core_year_more) >= 2) return 4;
        return null;
    }

    function get_score_culture_fit($culture_fit)
    {
        if ($culture_fit > 4) return 5;
        if ($culture_fit > 3) return 4;
        if ($culture_fit > 2) return 3;
        if ($culture_fit > 1) return 2;
        if ($culture_fit > 0) return 1;
        return null;
    }

    function get_score_age($age)
    {
        if ($age >= 55 && $age < 35) return 1;
        if ($age >= 50) return 2;
        if ($age >= 45) return 3;
        if ($age >= 40) return 4;
        if ($age >= 35) return 5;
        return null;
    }

    function get_score_health_status($health_status)
    {
        return $health_status;
    }

    function get_score_kategori_hav_mapping($kategori_hav_mapping)
    {
        if ($kategori_hav_mapping == 'Top Talent') return 5;
        if (in_array($kategori_hav_mapping, array('Promotable', 'Prostar 1', 'Prostar 2'))) return 4;
        if ($kategori_hav_mapping == 'Sleeping Tiger') return 3;
        if ($kategori_hav_mapping == 'Solid Contributor') return 2;
        if ($kategori_hav_mapping == 'Unfit') return 1;
        return null;
    }

    function get_score_assess_score($assess_score)
    {
        if ($assess_score) {
            if ($assess_score >= 80) return 5;
            if ($assess_score >= 60) return 3;
            return 1;
        }
        return null;
    }

    function get_score_correlation_matrix($correlation)
    {
        if ($correlation > 60) return 5;
        if ($correlation > 40) return 4;
        if ($correlation > 20) return 3;
        if ($correlation > 0) return 2;
        return null;
    }
}
