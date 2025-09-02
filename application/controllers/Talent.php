<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Talent extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_talent', 'm_tal');
    }

    public function index()
    {
        $data['levels'] = $this->db->get_where('org_area_lvl', array('equals' => null))->result_array();
        array_pop($data['levels']); // remove last row
        $data['content'] = "talent/level_list";
        $this->load->view('templates/header_footer', $data);
    }

    public function candidate_list($level_hash)
    {
        $data['employees'] = $this->get_candidate($level_hash);
        $data['level'] = $this->db->get_where('org_area_lvl', array('md5(id)' => $level_hash))->row_array();
        $data['content'] = "talent/candidate_list";
        $this->load->view('templates/header_footer', $data);
    }

    public function get_candidate($level_hash)
    {
        $level = $this->db->get_where('org_area_lvl', array('md5(id)' => $level_hash))->row_array();
        if ($level) {
            $candidate_level = $level['id'] + 1;
            $candidate_level = $this->db->where('id', $candidate_level)->or_where('equals', $candidate_level)->get('org_area_lvl')->result_array();
            $candidate_level_ids = array_column($candidate_level, 'id'); // ambil semua value kolom 'id'
            $candidate_level_ids_string = implode(',', $candidate_level_ids);    // gabung jadi string

            $this->load->model('m_hav');
            $this->load->model('organization/m_position');

            $employees = $this->db->query("
                SELECT 
                    u.NRP, u.FullName,
                    a.assess_score, a.recommendation, FORMAT(a.job_fit_score, 2) job_fit_score,
                    FORMAT(p.avg_ipa_score, 2) avg_ipa_score, FORMAT(p.ipa_score, 2) ipa_score,
                    TIMESTAMPDIFF(YEAR, u.BirthDate, CURDATE()) age,
                    oal.name level_name, oal.id oal_id,
                    clam.name clam_name,
                    null kompetensi_teknis,
                    null score_kompetensi_teknis,
                    null tour_of_duty,
                    null status_kesehatan,
                    null score_status_kesehatan,
                    null rekomendasi_assessment,
                    null status_kesehatan,
                    null culture_fit
                    FROM rml_sso_la.users u
                LEFT JOIN (
                    SELECT * FROM (
                        SELECT 
                            cla.NRP,
                            MAX(cla.score) AS assess_score,
                            MAX(cla.recommendation) AS recommendation,
                            SUM(IFNULL(clas.score, 0)) / 10 AS job_fit_score,
                            cla.tahun,
                            ROW_NUMBER() OVER (
                                PARTITION BY cla.NRP 
                                ORDER BY cla.tahun DESC, cla.method_id
                            ) AS rn,
                            method_id
                        FROM comp_lvl_assess_score clas
                        LEFT JOIN comp_lvl_assess cla ON cla.id = clas.comp_lvl_assess_id
                        WHERE cla.method_id IN (1, 2)
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
                LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
                LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
                LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
                LEFT JOIN org_area oa ON oa.id = oalp.area_id
                LEFT JOIN comp_lvl_assess_method clam ON clam.id = a.method_id
                WHERE oal.id IN($candidate_level_ids_string);
            ")->result_array();
            $employees = $this->m_hav->calculate_hav_status($employees);
            $employees = $this->m_tal->calculate_candidate_scores($employees);
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
}
