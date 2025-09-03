<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('employee/m_employee', 'm_emp');
        $this->load->model('competency/m_comp_level', 'm_c_lvl');
        $this->load->model('competency/m_comp_level_target', 'm_c_l_targ');
        $this->load->model('competency/m_comp_position', 'm_c_pstn');
        $this->load->model('competency/m_comp_position_target', 'm_c_p_targ');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
    }

    public function index()
    {
        $data['employees'] = $this->m_emp->get_employee();
        $data['content'] = "employee/list";
        $this->load->view('templates/header_footer', $data);
    }

    public function profile($NRP_hash)
    {
        $user = $this->m_user->get_user($NRP_hash, 'md5(NRP)', false);
        $position = $this->m_user->get_area_lvl_pstn_user($user['NRP'], 'NRP', false);

        $data['user'] = $user;
        $data['position'] = null;
        $data['comp_pstn'] = null;
        $data['comp_pstn_targ'] = null;
        $data['matrix_point'] = null;
        $data['competency_matrix_position'] = null;
        $data['comp_lvl'] = null;
        $data['comp_lvl_targ'] = null;
        $data['competency_matrix_level'] = null;

        if ($position) {
            $user_oalp_id_md5 = md5($position['area_lvl_pstn_id']);
            $position = $this->m_pstn->get_area_lvl_pstn($user_oalp_id_md5, 'md5(oalp.id)', false);
            $data['position'] = $position;
            $position_id_md5 = md5($position['id']);

            $superiors = array_reverse($this->m_pstn->get_superiors($position_id_md5));
            $subordinates = $this->m_pstn->get_subordinates($user_oalp_id_md5);
            $subordinates = array_shift($subordinates);

            // Cari matrix_point dari atasan
            $superior_matrix_point = array_values(array_filter($superiors, function ($sup) use ($position) {
                return $sup['type'] === "matrix_point" && $sup['id'] != $position['id'];
            }));

            if ($position['type'] == "matrix_point") {
                $matrix_point = $position;
            } elseif (!empty($superior_matrix_point)) {
                $mtxp = end($superior_matrix_point);

                if ($mtxp['area_id'] == $position['oa_id']) {
                    $matrix_point = $mtxp;
                } else {
                    $matrix_point_from_superior = array_values(array_filter($superiors, fn($s) => !empty($s['matrix_point'])));
                    $matrix_points_subordinates = array_values(array_filter($subordinates, fn($s) => !empty($s['matrix_point'])));

                    if (empty($matrix_point_from_superior)) {
                        $matrix_point = $mtxp;

                        $mtxp_sup_ids = array_unique(array_column($matrix_points_subordinates, 'matrix_point'));
                        if (!empty($mtxp_sup_ids)) {
                            $ids_str = implode(',', $mtxp_sup_ids);
                            $matrix_point_super = $this->db->query("SELECT * FROM org_area_lvl_pstn WHERE id IN($ids_str)")->result_array();

                            foreach ($matrix_point_super as $mtxps_i) {
                                $subordinate_ids = array_filter($matrix_points_subordinates, fn($s) => $s['matrix_point'] == $mtxps_i['id']);
                                $mtxps_i['subordinates'] = [];

                                foreach ($subordinate_ids as $sbmp_i) {
                                    $mtxps_i['subordinates'] = array_merge(
                                        $mtxps_i['subordinates'],
                                        $this->m_pstn->get_subordinates(md5($sbmp_i['id']))
                                    );
                                }
                                $matrix_point = $mtxps_i;
                            }
                        }
                    } else {
                        $mtxp_sup_id = $matrix_point_from_superior[0]['matrix_point'];
                        $mtxp = $this->db->query("SELECT * FROM org_area_lvl_pstn WHERE id = $mtxp_sup_id")->row_array();
                        $matrix_point = $mtxp;
                    }
                }
            } else {
                $matrix_point = [];
            }
            if ($matrix_point) {
                $data['matrix_point'] = $matrix_point;
                $competencies = $this->m_c_pstn->get_comp_position($matrix_point['id'], 'area_lvl_pstn_id');
                $data['comp_pstn'] = $competencies;
                $comp_target = $this->m_c_p_targ->get_comp_position_target($position['id'], 'area_lvl_pstn_id');
                $data['comp_pstn_targ'] = $comp_target;
                $competency_matrix = $this->create_matrix_position($position, $competencies, $comp_target);
                $data['competency_matrix_position'] = $competency_matrix;
            }

            $competencies = $this->m_c_lvl->get_comp_level();
            $data['comp_lvl'] = $competencies;
            $comp_target = $this->m_c_l_targ->get_comp_level_target($position['id'], 'area_lvl_pstn_id');
            $competency_matrix = $this->create_matrix_level($position, $competencies, $comp_target);
            $data['competency_matrix_level'] = $competency_matrix;
        }
        $data['content'] = "general/profile";
        $this->load->view('templates/header_footer', $data);
    }

    public function create_matrix_position($position, $competencies, $targets)
    {
        foreach ($competencies as $i_cp => $cp_i) {
            $position['target'][$cp_i['id']] = 0;
            $target = array_filter($targets, fn($cpt_i, $i_cpt) => $cpt_i['comp_pstn_id'] == $cp_i['id'] && $cpt_i['area_lvl_pstn_id'] == $position['id'], ARRAY_FILTER_USE_BOTH);
            $target = array_values($target);
            if ($target) $position['target'][$cp_i['id']] = $target[0]['target'];
        }
        return $position;
    }

    public function create_matrix_level($position, $competencies, $targets)
    {
        foreach ($competencies as $i_cl => $cl_i) {
            $position['target'][$cl_i['id']] = 0;
            $target = array_filter($targets, fn($clt_i, $i_clt) => $clt_i['comp_lvl_id'] == $cl_i['id'] && $clt_i['area_lvl_pstn_id'] == $position['id'], ARRAY_FILTER_USE_BOTH);
            $target = array_values($target);
            if ($target) $position['target'][$cl_i['id']] = $target[0]['target'];
        }
        return $position;
    }
}
