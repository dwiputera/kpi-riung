<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Position_score extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_position_target', 'm_c_p_t');
        $this->load->model('competency/m_comp_position_score', 'm_c_p_s');
        $this->load->model('organization/m_position', 'm_pstn');
    }

    public function index()
    {
        $data['matrix_points'] = $this->db->get_where('org_area_lvl_pstn', array('type' => 'matrix_point'))->result_array();
        $data['content'] = "competency/position_score_matrix_point";
        $this->load->view('templates/header_footer', $data);
    }

    public function view($mp_hash)
    {
        $data['matrix_point'] = $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $mp_hash))->row_array();
        $comp_pstn = $this->db->get_where('comp_position', array('md5(area_lvl_pstn_id)' => $mp_hash))->result_array();
        $data['comp_pstn'] = $comp_pstn;
        $cp_scores = $this->m_c_p_s->get_cp_score($mp_hash, 'md5(area_lvl_pstn_id)');
        $cp_targets = $this->m_c_p_t->get_comp_position_target($mp_hash, 'md5(cp.area_lvl_pstn_id)');
        $employees = $this->m_pstn->get_area_lvl_pstn_user($mp_hash, 'md5(mp_id)');
        $data['employees'] = $this->create_matrix($employees, $comp_pstn, $cp_scores, $cp_targets);
        $data['content'] = "competency/position_score";
        $this->load->view('templates/header_footer', $data);
    }

    public function edit($mp_hash)
    {
        $data['matrix_point'] = $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $mp_hash))->row_array();
        $comp_pstn = $this->db->get_where('comp_position', array('md5(area_lvl_pstn_id)' => $mp_hash))->result_array();
        $data['comp_pstn'] = $comp_pstn;
        $cp_scores = $this->m_c_p_s->get_cp_score($mp_hash, 'md5(area_lvl_pstn_id)');
        $cp_targets = $this->m_c_p_t->get_comp_position_target($mp_hash, 'md5(cp.area_lvl_pstn_id)');
        $employees = $this->m_pstn->get_area_lvl_pstn_user($mp_hash, 'md5(mp_id)');
        $data['employees'] = $this->create_matrix($employees, $comp_pstn, $cp_scores, $cp_targets);
        $data['content'] = "competency/position_score_edit";
        $this->load->view('templates/header_footer', $data);
    }

    function create_matrix($employees, $comp_pstn, $cp_scores, $cp_targets)
    {
        foreach ($employees as &$e_i) {
            foreach ($comp_pstn as $i_cp => $cp_i) {
                $cp_id = $cp_i['id'];
                $e_i['cp_score'][$cp_id] = 0;
                $e_i['cp_target'][$cp_id] = 0;
                $score = array_filter($cp_scores, fn($cps_i, $i_cps) => $cps_i['NRP'] == $e_i['NRP'] && $cps_i['comp_pstn_id'] == $cp_id, ARRAY_FILTER_USE_BOTH);
                if ($score) {
                    $e_i['cp_score'][$cp_id] = array_shift($score)['score'];
                }
                $target = array_filter($cp_targets, fn($cpt_i, $i_cpt) => $cpt_i['cpt_oalp_id'] == $e_i['oalp_id'] && $cpt_i['comp_pstn_id'] == $cp_id, ARRAY_FILTER_USE_BOTH);
                if ($target) {
                    $e_i['cp_target'][$cp_id] = array_shift($target)['target'];
                }
            }
        }
        return $employees;
    }

    public function submit($mp_hash)
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Score Submit Failed"
        ]);
        $success = $this->m_c_p_s->submit();
        if ($success) {
            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "Score Submitted Successfully"
            ]);
        }
        redirect("comp_settings/position_score/view/$mp_hash");
    }
}
