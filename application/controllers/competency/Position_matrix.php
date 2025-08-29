<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Position_matrix extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_position', 'm_c_pstn');
        $this->load->model('competency/m_comp_position_target', 'm_c_p_targ');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
    }

    public function index()
    {
        $NRP = $this->session->userdata('NRP');
        $user = $this->m_user->get_area_lvl_pstn_user($NRP, 'NRP', false);
        $matrix_points = [];

        if ($user) {
            $user_oalp_id_md5 = md5($user['area_lvl_pstn_id']);
            $position = $this->m_pstn->get_area_lvl_pstn($user_oalp_id_md5, 'md5(oalp.id)', false);
            $position_id_md5 = md5($position['id']);

            $superiors = array_reverse($this->m_pstn->get_superiors($position_id_md5));
            $subordinates = $this->m_pstn->get_subordinates($user_oalp_id_md5);

            // Cari matrix_point dari atasan
            $superior_matrix_point = array_values(array_filter($superiors, function ($sup) use ($position) {
                return $sup['type'] === "matrix_point" && $sup['id'] != $position['id'];
            }));

            if (!empty($superior_matrix_point)) {
                $mtxp = $superior_matrix_point[0];

                if ($mtxp['area_id'] == $user['oa_id']) {
                    $mtxp['subordinates'] = $this->m_pstn->get_subordinates($position_id_md5, 'with_without_matrix');
                    $matrix_points[] = $mtxp;
                } else {
                    $matrix_point_from_superior = array_values(array_filter($superiors, fn($s) => !empty($s['matrix_point'])));
                    $matrix_points_subordinates = array_values(array_filter($subordinates, fn($s) => !empty($s['matrix_point'])));

                    if (empty($matrix_point_from_superior)) {
                        $mtxp['subordinates'] = $this->m_pstn->get_subordinates($position_id_md5, 'without_matrix');
                        $matrix_points[] = $mtxp;

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
                                $matrix_points[] = $mtxps_i;
                            }
                        }
                    } else {
                        $mtxp_sup_id = $matrix_point_from_superior[0]['matrix_point'];
                        $mtxp = $this->db->query("SELECT * FROM org_area_lvl_pstn WHERE id = $mtxp_sup_id")->row_array();
                        $mtxp['subordinates'] = $this->m_pstn->get_subordinates($position_id_md5);
                        $matrix_points[] = $mtxp;
                    }
                }
            } else {
                $matrix_points_subordinates = array_values(array_filter($subordinates, fn($s) => $s['type'] === "matrix_point"));
                foreach ($matrix_points_subordinates as $mtxp_i) {
                    $mtxp_i['subordinates'] = $this->m_pstn->get_subordinates(md5($mtxp_i['id']), 'with_without_matrix');
                    $matrix_points[] = $mtxp_i;
                }
            }
        }

        // Data pelengkap
        $competencies = $this->m_c_pstn->get_comp_position();
        $targets = $this->m_c_p_targ->get_comp_position_target();
        $data['matrix_points'] = $this->create_matrix($matrix_points, $competencies, $targets);

        // Filter dict berdasarkan matrix_points
        $comp_pstn_dicts = $this->db->query("
            SELECT * FROM comp_pstn_dict cpd
            LEFT JOIN comp_position cp ON cp.id = cpd.comp_pstn_id
        ")->result_array();

        $matrix_point_ids = array_column($matrix_points, 'id');
        $data['comp_pstn_dicts'] = array_filter($comp_pstn_dicts, fn($cpd) => in_array($cpd['area_lvl_pstn_id'], $matrix_point_ids));

        $data['admin'] = false;
        $data['matrix_position_active'] = $this->input->get('matrix_position_active');
        $data['competencies'] = $this->group_competencies($matrix_points, $competencies);
        $data['content'] = "competency/position_matrix";
        $this->load->view('templates/header_footer', $data);
    }

    // public function index()
    // {
    //     $NRP = $this->session->userdata('NRP');
    //     $user = $this->m_user->get_area_lvl_pstn_user($NRP, 'NRP', false);
    //     $position = [];
    //     $pstn_matrix_point = [];
    //     $subordinates = [];
    //     $superiors = [];
    //     $superior_matrix_point = [];
    //     if ($user) {
    //         $position = $this->m_pstn->get_area_lvl_pstn(md5($user['area_lvl_pstn_id']), 'md5(oalp.id)', false);
    //         $superiors = array_reverse($this->m_pstn->get_superiors(md5($position['id'])));
    //         $subordinates = $this->m_pstn->get_subordinates(md5($user['area_lvl_pstn_id']));
    //         $superior_matrix_point = array_filter($superiors, fn($sup_i, $i_sup) => $sup_i['type'] == "matrix_point" && $sup_i['id'] != $position['id'], ARRAY_FILTER_USE_BOTH);
    //     }
    //     $matrix_points = [];
    //     if ($superior_matrix_point) {
    //         $mtxp = array_values($superior_matrix_point)[0];
    //         if ($mtxp['area_id'] == $user['oa_id']) {
    //             $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5($position['id']));
    //             $matrix_points[] = $mtxp;
    //         } else {
    //             $superior_matrix_point = array_filter($superiors, fn($sup_i, $i_sup) => $sup_i['matrix_point'], ARRAY_FILTER_USE_BOTH);
    //             $matrix_points_subordinates = array_filter($subordinates, fn($sub_i, $i_sub) => $sub_i['matrix_point'], ARRAY_FILTER_USE_BOTH);
    //             if (!$superior_matrix_point) {
    //                 $mtxp['subordinates'] = $this->m_pstn->get_subordinates_without_matrix_points(md5($position['id']));
    //                 $matrix_points[] = $mtxp;
    //                 $mtxp_sup = array_column($matrix_points_subordinates, 'matrix_point');
    //                 $mtxp_sup = implode(',', $mtxp_sup);
    //                 $matrix_point_super = $this->db->query("
    //                     SELECT * FROM org_area_lvl_pstn oalp
    //                     WHERE id IN($mtxp_sup)
    //                 ")->result_array();
    //                 foreach ($matrix_point_super as $i_mtxps => $mtxps_i) {
    //                     $mtxp = $mtxps_i;
    //                     $subordinate_by_matrix_point = array_filter($matrix_points_subordinates, fn($mps_i, $i_mps) => $mps_i['matrix_point'] == $mtxps_i['id'], ARRAY_FILTER_USE_BOTH);
    //                     $mtxp['subordinates'] = [];
    //                     foreach ($subordinate_by_matrix_point as $i_sbmp => $sbmp_i) {
    //                         $mtxp_subordinates = $this->m_pstn->get_subordinates(md5($sbmp_i['id']));
    //                         $mtxp['subordinates'] = array_merge($mtxp['subordinates'], $mtxp_subordinates);
    //                     }
    //                     $matrix_points[] = $mtxp;
    //                 }
    //             } else {
    //                 $mtxp_sup = array_values($superior_matrix_point)[0]['matrix_point'];
    //                 $matrix_point_super = $this->db->query("
    //                     SELECT * FROM org_area_lvl_pstn oalp
    //                     WHERE id = $mtxp_sup
    //                 ")->row_array();
    //                 $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5($position['id']));
    //                 $matrix_points[] = $mtxp;
    //             }
    //         }
    //     } else {
    //         $matrix_points_subordinates = array_filter($subordinates, fn($sub_i, $i_sub) => $sub_i['type'] == "matrix_point", ARRAY_FILTER_USE_BOTH);
    //         foreach ($matrix_points_subordinates as $i_mtxp => $mtxp_i) {
    //             $mtxp = $mtxp_i;
    //             $mtxp['subordinates'] = $this->m_pstn->get_subordinates_with_without_matrix_points(md5($mtxp_i['id']));
    //             $matrix_points[] = $mtxp;
    //         }
    //     }

    //     $competencies = $this->m_c_pstn->get_comp_position();
    //     $targets = $this->m_c_p_targ->get_comp_position_target();
    //     $data['matrix_points'] = $this->create_matrix($matrix_points, $competencies, $targets);
    //     $comp_pstn_dicts = $this->db->query("
    //         SELECT * FROM comp_pstn_dict cpd
    //         LEFT JOIN comp_position cp ON cp.id = cpd.comp_pstn_id
    //     ")->result_array();
    //     $data['comp_pstn_dicts'] = array_filter($comp_pstn_dicts, fn($cpd_i, $i_cpd) => in_array($cpd_i['area_lvl_pstn_id'], array_column($matrix_points, 'id')), ARRAY_FILTER_USE_BOTH);
    //     $data['admin'] = false;
    //     $data['matrix_position_active'] = $this->input->get('matrix_position_active');
    //     $data['competencies'] = $this->group_competencies($matrix_points, $competencies);
    //     $data['content'] = "competency/position_matrix";
    //     $this->load->view('templates/header_footer', $data);
    // }

    public function create_matrix($matrix_points, $competencies, $targets)
    {
        foreach ($matrix_points as $i_mtxp => $mtxp_i) {
            foreach ($mtxp_i['subordinates'] as $i_oalp => $oalp_i) {
                foreach ($competencies as $i_cp => $cp_i) {
                    $matrix_points[$i_mtxp]['subordinates'][$i_oalp]['target'][$cp_i['id']] = 0;
                    $target = array_filter($targets, fn($cpt_i, $i_cpt) => $cpt_i['comp_pstn_id'] == $cp_i['id'] && $cpt_i['area_lvl_pstn_id'] == $oalp_i['id'], ARRAY_FILTER_USE_BOTH);
                    $target = array_values($target);
                    if ($target) $matrix_points[$i_mtxp]['subordinates'][$i_oalp]['target'][$cp_i['id']] = $target[0]['target'];
                }
            }
        }
        return $matrix_points;
    }

    public function group_competencies($matrix_points, $competencies)
    {
        $comp_positions = [];
        foreach ($matrix_points as $i_mtxp => $mtxp_i) {
            $comp_positions[$mtxp_i['id']] = array_filter($competencies, fn($cp_i, $i_cp) => $cp_i['area_lvl_pstn_id'] == $mtxp_i['id'], ARRAY_FILTER_USE_BOTH);
        }
        return $comp_positions;
    }

    public function dictionary($hash_pstn_id)
    {
        $data['position'] = $this->m_pstn->get_area_lvl_pstn($hash_pstn_id, 'md5(oalp.id)', false);
        $data['dictionaries'] = $this->m_c_pstn->get_comp_position($hash_pstn_id, 'md5(area_lvl_pstn_id)');
        $data['admin'] =  false;
        $data['content'] = "competency/position_dictionary";
        $this->load->view('templates/header_footer', $data);
    }
}
