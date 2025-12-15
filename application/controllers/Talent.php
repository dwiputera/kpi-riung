<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Talent extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_talent', 'm_tal');
        $this->load->model('organization/M_position', 'm_p');
        $this->load->model('employee/M_employee', 'm_e');
        $this->load->model('M_health_status', 'm_hs');
        $this->load->model('competency/M_comp_level', 'm_cl');
        $this->load->model('competency/M_comp_level_target', 'm_clt');
        $this->load->model('competency/M_comp_lvl_assess', 'm_cla');
        $this->load->model('competency/M_comp_level_score', 'm_cls');
        $this->load->model('competency/M_comp_position', 'm_cp');
        $this->load->model('competency/M_comp_position_target', 'm_cpt');
        $this->load->model('competency/M_comp_position_score', 'm_cps');
        $this->load->model('training/M_mts_user', 'm_mu');
        $this->load->model('training/M_atmp_user', 'm_au');
        $this->load->model('M_hav_rcrd', 'm_hr');
        $this->load->model('M_tour_of_duty', 'm_tod');
        $this->load->model('M_rtc');
    }

    public function index()
    {
        $data['positions'] = $this->m_p->get_area_lvl_pstn([1, 2, 3, 4, 6, 7, 8, 9], 'oal.id');
        $data['content'] = "talent/position_list";
        $this->load->view('templates/header_footer', $data);
    }

    public function candidate_list($pstn_hash)
    {
        $method_id = $this->input->get('method') == 'PR' ? 2 : 1;
        $position = $this->m_p->get_area_lvl_pstn($pstn_hash, 'md5(oalp.id)', false);
        $data['position'] = $position;
        $data['method'] = $this->input->get('method');
        $data['employees'] = $this->m_tal->get_candidate($position, $method_id);
        $data['percentage'] = $this->m_tal->get_percentage($position['oal_id']);
        $data['content'] = "talent/candidate_list";
        $this->load->view('templates/header_footer', $data);
    }

    public function profile($position_id_hash, $nrp_hash)
    {
        $year = date('Y');
        $month = date('n');
        $method_id = $this->input->get('method') == 'PR' ? 2 : 1;
        $employee = $this->m_e->get_employee($nrp_hash, 'md5(users.NRP)', false);
        $correlation_matrix = $this->m_cp->get_correlation_matrix();
        $matrix_points = $this->db->get_where('org_area_lvl_pstn', ['type' => 'matrix_point'])->result_array();
        $correlation_matrixes = array_filter($correlation_matrix, fn($mp) => $mp['id'] == $employee['mp_id']);
        if ($correlation_matrixes) $correlation_matrixes = array_values($correlation_matrixes)[0];
        $correlation_matrix = [];
        foreach ($matrix_points as $i_mp => $mp_i) {
            $correlation = array_filter($correlation_matrixes['correlations'], fn($cor_i, $i_cor) => $i_cor == $mp_i['id'], ARRAY_FILTER_USE_BOTH);
            if ($correlation) {
                $correlation = array_values($correlation)[0];
            } else {
                $correlation = 0;
            }
            $correlation_matrix[$mp_i['id']] = [
                'oalp_name' => $mp_i['name'],
                'correlation' => $correlation
            ];
        }

        $correlation_matrix = array_filter($correlation_matrix, function ($row) {
            return $row['correlation'] > 0;
        });

        usort($correlation_matrix, function ($a, $b) {
            return $b['correlation'] <=> $a['correlation'];
        });

        $data['correlation_matrix'] = $correlation_matrix;
        $data['rtc'] = $this->M_rtc->get("
            WHERE NRP = '$employee[NRP]' 
            AND md5(oalp_id) = '$position_id_hash' 
            AND rtc.year >= $year 
            ORDER BY rtc.year ASC
            LIMIT 1
        ", false);
        $data['tour_of_duties'] = $this->m_tod->get_tod_with_mp("WHERE NRP = '$employee[NRP]' ORDER BY date DESC");
        $data['target_position'] = $this->m_p->get_area_lvl_pstn($position_id_hash, 'md5(oalp.id)', false);
        $data['comp_lvl'] = $this->m_cl->get_comp_level();
        $data['comp_lvl_targets'] = $this->m_clt->get_comp_level_target($data['target_position']['id'], 'area_lvl_pstn_id');
        $data['comp_lvl_assess'] = $this->m_cla->get_comp_lvl_emp_assess($employee['NRP'], "method_id = $method_id AND cla.NRP", false);
        $data['comp_lvl_scores'] = $this->m_cls->get_cl_score_latest("WHERE cla.NRP = $employee[NRP] AND method_id = $method_id AND cla.NRP");
        $data['comp_pstn_targets'] = $this->m_cpt->get_comp_position_target($employee['oalp_id'], 'cpt.area_lvl_pstn_id');
        $data['comp_pstn_scores'] = $this->m_cps->get_cp_score($employee['NRP'], 'cps_mxyr.NRP');
        $data['method'] = $this->db->get_where('comp_lvl_assess_method', array('id' => $method_id))->row_array()['name'];
        $data['employee'] = $employee;
        $data['mts'] = $this->m_mu->get_user_mts("WHERE NRP = '$employee[NRP]' ORDER BY YEAR DESC, MONTH DESC, start_date DESC, tm.id DESC");
        $data['atmp'] = $this->m_au->get_user_atmp("
            WHERE NRP = '$employee[NRP]' 
            AND (
                year > $year
                OR (
                    year = $year 
                    AND month >= $month
                )
            )");
        $candidates = $this->m_tal->get_candidate($data['target_position'], $method_id);
        $candidate = array_filter($candidates, fn($cnddt) => md5($cnddt['NRP']) == $nrp_hash);
        $data['candidate'] = array_shift($candidate);
        $data['health_statuses'] = $this->m_hs->get("WHERE md5(hsu.NRP) = '$nrp_hash'");
        $data['ipa_scores'] = $this->db->get_where('emp_ipa_score', array('md5(NRP)' => $nrp_hash))->result_array();
        $data['hav_map'] = $this->m_hr->get("WHERE NRP = '$employee[NRP]' AND method_id = $method_id");
        $data['content'] = "talent/profile";
        $this->load->view('templates/header_footer', $data);
    }
}
