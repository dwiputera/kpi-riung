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
        // $NRP = '10007005'; //afify
        // $NRP = '10106006'; //pa eko
        // $NRP = '10122289'; //ceu shanty
        // $NRP = '10109069'; //pa levy
        // $NRP = '10111396'; //pa fajri
        // $NRP = '10124038'; //uda defri
        // $NRP = '10125097'; //engineering
        // $NRP = '10106007'; //prod_ops HO
        // $NRP = '10109088'; //prod_ops area_1
        // $NRP = '10112853'; //prod_ops area_2
        // $NRP = '10112726'; //PM REBH
        // $NRP = '10106010'; //REBH dept head ENGINEERING
        // $NRP = '10121386'; //REBH sect head ENGINEERING-survey&moco
        $position = $this->m_pstn->get_area_lvl_pstn(md5(1), 'md5(oalp.id)', false);
        // $pstn_matrix_point = $this->m_c_pstn->get_pstn_matrix_point();
        $subordinates = $this->m_pstn->get_subordinates(md5(1));
        $superiors = array_reverse($this->m_pstn->get_superiors(md5($position['id'])));
        // $superior_matrix_point = array_filter($superiors, fn($sup_i, $i_sup) => $sup_i['area_lvl_id'] == $pstn_matrix_point['id'] && $sup_i['id'] != $position['id'], ARRAY_FILTER_USE_BOTH);
        $superior_matrix_point = array_filter($superiors, fn($sup_i, $i_sup) => $sup_i['type'] == "matrix_point" && $sup_i['id'] != $position['id'], ARRAY_FILTER_USE_BOTH);
        $matrix_points = [];
        if ($superior_matrix_point) {
            $mtxp = array_values($superior_matrix_point)[0];
            $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5($position['id']));
            $matrix_points[] = $mtxp;
            // $this->m_pstn->get_subordinates(md5($position['id']));
        } else {
            // $matrix_points_subordinates = array_filter($subordinates, fn($sub_i, $i_sub) => $sub_i['area_lvl_id'] == $pstn_matrix_point['id'], ARRAY_FILTER_USE_BOTH);
            $matrix_points_subordinates = array_filter($subordinates, fn($sub_i, $i_sub) => $sub_i['type'] == "matrix_point", ARRAY_FILTER_USE_BOTH);
            foreach ($matrix_points_subordinates as $i_mtxp => $mtxp_i) {
                $mtxp = $mtxp_i;
                // $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5($mtxp_i['id']));
                $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5($mtxp_i['id']), 'with_without_matrix');
                $matrix_points[] = $mtxp;
            }
        }

        $competencies = $this->m_c_pstn->get_comp_position();
        $targets = $this->m_c_p_targ->get_comp_position_target();
        $data['matrix_points'] = $this->create_matrix($matrix_points, $competencies, $targets);
        $data['admin'] = true;
        $data['matrix_position_active'] = $this->input->get('matrix_position_active');
        $data['competencies'] = $this->group_competencies($matrix_points, $competencies);
        $data['content'] = "competency/position_matrix";
        $this->load->view('templates/header_footer', $data);
    }

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

    public function comp_pstn_target($action)
    {
        $matrix_position_active = '';
        if ($this->input->get('matrix_position_active')) {
            $matrix_position_active = '?matrix_position_active=' . $this->input->get('matrix_position_active');
        }

        switch ($action) {
            case 'submit':
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => "Target Score Submit Failed"
                ]);

                // Ubah: gunakan result dari model sebagai penentu
                $success = $this->m_c_p_targ->submit();

                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "Target Score Submitted Successfully"
                    ]);
                }

                redirect('comp_settings/position_matrix' . $matrix_position_active);
                break;

            default:
                show_404();
                break;
        }
    }


    public function comp_pstn($action, $hash_id = null, $hash_id2 = null)
    {
        $matrix_position_active = '';
        if ($this->input->get('matrix_position_active')) $matrix_position_active = '?matrix_position_active=' . $this->input->get('matrix_position_active');
        if (!$matrix_position_active) {
            if ($this->input->post('hash_area_lvl_pstn_id')) {
                $matrix_position_active = '?matrix_position_active=' . $this->input->post('hash_area_lvl_pstn_id');
            } elseif ($this->input->post('hash_comp_pstn_id')) {
                $comp_pstn = $this->m_c_pstn->get_comp_position($this->input->post('hash_area_lvl_pstn_id'), 'md5(id)', false);
                $matrix_position_active = '?matrix_position_active=' . md5($comp_pstn['area_lvl_pstn_id']);
            }
        }
        switch ($action) {
            case 'add':
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => "Target add Failed"
                ]);
                $success = $this->m_c_pstn->add();
                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "Target added Successfully"
                    ]);
                }
                break;

            case 'edit':
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => "Target edit Failed"
                ]);
                $success = $this->m_c_pstn->edit();
                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "Target edited Successfully"
                    ]);
                }
                break;

            case 'delete':
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => "Competency Delete Failed"
                ]);
                $success = $this->m_c_pstn->delete($hash_id);
                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "Competency Deleted Successfully"
                    ]);
                }
                break;

            default:
                show_404();
                break;
        }
        redirect('comp_settings/position_matrix' . $matrix_position_active);
    }

    public function import_comp_pstn($sheet = 0)
    {
        $sheets = [1 => "engineering"];
        $this->load->helper('conversion');
        $this->load->helper('extract_spreadsheet');
        $sheets = extract_spreadsheet('./uploads/imports_admin/pstn_matrix_import.xlsx');
        $this->db->query('TRUNCATE TABLE comp_position');
        $this->db->query('TRUNCATE TABLE comp_pstn_target');

        foreach ($sheets as $i_sht => $rows) {
            $comp_data_insert = [];
            $area_lvl_pstn_id = $rows[0][0];
            if (isset($rows[2])) {
                $columns = array_filter($rows[2], fn($col_i, $i_col) => $i_col >= 3 && $col_i, ARRAY_FILTER_USE_BOTH);
                foreach ($columns as $i_col => $col_i) {
                    $data = [
                        "area_lvl_pstn_id" => $area_lvl_pstn_id,
                        "name" => $col_i,
                    ];
                    $comp_data_insert[] = $data;
                }
                if ($comp_data_insert) echo $this->db->insert_batch('comp_position', $comp_data_insert);

                $comp_positions = $this->db->where("area_lvl_pstn_id", $area_lvl_pstn_id)->get('comp_position')->result_array();
                $comp_positions = array_values($comp_positions);

                $comp_target_data_insert = [];
                $rows = array_filter($rows, fn($row_i, $i_row) => $i_row >= 3 && $row_i && $row_i[0], ARRAY_FILTER_USE_BOTH);
                foreach ($rows as $i_row => $row_i) {
                    $pstns = explode(",", $row_i[0]);
                    foreach ($pstns as $i_pstn => $pstn_i) {
                        $targets = array_filter($row_i, fn($col_i, $i_col) => $i_col >= 3 && $col_i, ARRAY_FILTER_USE_BOTH);
                        $targets = array_values($targets);
                        foreach ($targets as $i_targ => $targ_i) {
                            $data = [
                                "area_lvl_pstn_id" => $pstn_i,
                                "comp_pstn_id" => $comp_positions[$i_targ]['id'],
                                "target" => $targ_i,
                            ];
                            $comp_target_data_insert[] = $data;
                        }
                    }
                }
                if ($comp_target_data_insert) echo $this->db->insert_batch('comp_pstn_target', $comp_target_data_insert);
            }
        }
    }

    public function import_comp_dictionary()
    {
        $this->db->query('TRUNCATE TABLE comp_pstn_dict');
        $this->load->helper('conversion');
        $this->load->helper('extract_spreadsheet');
        $sheets = extract_spreadsheet('./uploads/imports_admin/position_dictionary_competency.xlsx');
        $sheet = $sheets[0];
        $comp_pstn_fetch = $this->db->get('comp_position')->result_array();
        $comp_pstn_dict_insert = [];
        for ($i = 0; $i < 740; $i += 5) {
            $comp_pstn_name = $sheet[$i][2];
            $comp_pstns = array_filter($comp_pstn_fetch, fn($cpf_i, $i_cpf) => strcasecmp($cpf_i['name'], $comp_pstn_name) === 0, ARRAY_FILTER_USE_BOTH);
            if ($comp_pstns) {
                foreach ($comp_pstns as $i_cp => $cp_i) {
                    $data = [
                        "comp_pstn_id" => $cp_i['id'],
                        "definition" => $sheet[$i][3],
                        "level_1" => $sheet[$i][5],
                        "level_2" => $sheet[$i + 1][5],
                        "level_3" => $sheet[$i + 2][5],
                        "level_4" => $sheet[$i + 3][5],
                        "level_5" => $sheet[$i + 4][5],
                    ];
                    $comp_pstn_dict_insert[] = $data;
                }
            } else {
                $continue = ['Customer Satisfaction', 'Salesmanship', 'Business Requirement Analysis', 'Culture Management'];
                if (in_array($comp_pstn_name, $continue)) {
                    continue;
                }
            }
        }

        if ($comp_pstn_dict_insert) echo $this->db->insert_batch('comp_pstn_dict', $comp_pstn_dict_insert);
        $query = $this->db->query("
            SELECT * FROM comp_position cp
            LEFT JOIN comp_pstn_dict cpd ON cpd.comp_pstn_id = cp.id
            WHERE definition IS NULL
        ")->result_array();
        echo '<pre>', var_dump($query);
        die;
    }
}
