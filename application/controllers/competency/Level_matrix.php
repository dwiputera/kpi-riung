<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Level_matrix extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_level', 'm_c_lvl');
        $this->load->model('competency/m_comp_level_target', 'm_c_l_targ');
        $this->load->model('organization/m_level', 'm_lvl');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
    }

    public function index()
    {
        $NRP = $this->session->userdata('NRP');
        $user = $this->m_user->get_area_lvl_pstn_user($NRP, 'NRP', false);
        $data  = [
            'area_pstns' => [],
            'area_lvls' => [],
            'comp_levels' => [],
        ];
        $data['admin'] = false;
        $data['level_active'] = $this->input->get('level_active');
        if ($user) {
            $positions = $this->m_pstn->get_subordinates(md5($user['area_lvl_pstn_id']), 'with_matrix');
            $competencies = $this->m_c_lvl->get_comp_level();
            $targets = $this->m_c_l_targ->get_comp_level_target();
            $data['area_pstns'] = $this->create_matrix($positions, $competencies, $targets);
            $ids = array_keys(array_column($data['area_pstns'], null, 'oal_id'));

            $area_levels = array_values(array_filter($data['area_pstns'], function ($lvl) use ($ids) {
                return empty($lvl['equals']) || !in_array($lvl['equals'], $ids);
            }));
            $data['area_lvls'] = array_values(array_column($area_levels, null, 'oal_id'));
            $data['comp_levels'] = $this->m_c_lvl->get_comp_level();
        }
        $data['content'] = "competency/level_matrix";
        $this->load->view('templates/header_footer', $data);
    }

    public function create_matrix($positions, $competencies, $targets)
    {
        foreach ($positions as $i_oalp => $oalp_i) {
            foreach ($competencies as $i_cl => $cl_i) {
                $positions[$i_oalp]['target'][$cl_i['id']] = 0;
                $target = array_filter($targets, fn($clt_i, $i_clt) => $clt_i['comp_lvl_id'] == $cl_i['id'] && $clt_i['area_lvl_pstn_id'] == $oalp_i['id'], ARRAY_FILTER_USE_BOTH);
                $target = array_values($target);
                if ($target) $positions[$i_oalp]['target'][$cl_i['id']] = $target[0]['target'];
            }
        }
        return $positions;
    }

    public function dictionary()
    {
        $data['dictionaries'] = $this->m_c_lvl->get_comp_level();
        $data['content'] = "competency/level_dictionary";
        $this->load->view('templates/header_footer', $data);
    }
}
