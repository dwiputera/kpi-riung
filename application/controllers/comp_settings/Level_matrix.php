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
        $positions = $this->m_pstn->get_subordinates(md5(1));
        $competencies = $this->m_c_lvl->get_comp_level();
        $targets = $this->m_c_l_targ->get_comp_level_target();
        $data['admin'] = true;
        $data['level_active'] = $this->input->get('level_active');
        $data['area_pstns'] = $this->create_matrix($positions, $competencies, $targets);
        $area_levels = array_filter($data['area_pstns'], fn($ap_i, $i_ap) => !$ap_i['equals'], ARRAY_FILTER_USE_BOTH);
        $data['area_lvls'] = array_values(array_column($area_levels, null, 'oal_id'));
        $data['comp_levels'] = $this->m_c_lvl->get_comp_level();
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

    public function comp_lvl_target($action)
    {
        $level_active = '';
        if ($this->input->get('level_active')) {
            $level_active = '?level_active=' . $this->input->get('level_active');
        }

        switch ($action) {
            case 'submit':
                $success = $this->m_c_l_targ->submit();

                $this->session->set_flashdata('swal', [
                    'type' => $success ? 'success' : 'error',
                    'message' => $success ? 'Target Score Submitted Successfully' : 'Target Score Submit Failed'
                ]);

                redirect('comp_settings/level_matrix' . $level_active);
                break;

            default:
                show_404();
                break;
        }
    }


    public function comp_lvl($action, $hash_id = null, $hash_id2 = null)
    {
        $level_active = '';
        if ($this->input->get('level_active')) $level_active = '?level_active=' . $this->input->get('level_active');
        if (!$level_active && $this->input->post('hash_area_lvl_id')) {
            $area_lvl = $this->m_lvl->get_area_lvl($this->input->post('hash_area_lvl_id'), 'md5(oal.id)', false);
            $level_active = '?level_active=' . md5($area_lvl['id']);
        }
        switch ($action) {
            case 'add':
                flash_swal('error', 'Target add Failed');
                $success = $this->m_c_lvl->add();
                if ($success) {
                    flash_swal('success', 'Target added Successfully');
                }
                break;

            case 'edit':
                flash_swal('error', 'Target edit Failed');
                $success = $this->m_c_lvl->edit();
                if ($success) {
                    flash_swal('success', 'Target edited Successfully');
                }
                break;

            case 'delete':
                flash_swal('error', 'Competency Delete Failed');
                $success = $this->m_c_lvl->delete($hash_id);
                if ($success) {
                    flash_swal('success', 'Competency Deleted Successfully');
                }
                break;

            default:
                show_404();
                break;
        }
        redirect('comp_settings/level_matrix' . $level_active);
    }

    public function import_comp_lvl($sheet = 0)
    {
        $this->load->helper('conversion');
        $this->load->helper('extract_spreadsheet');
        $sheets = extract_spreadsheet('./uploads/imports_admin/lvl_matrix_import.xlsx', $sheet);
        $data_inserts = [];
        $data_updates = [];
        foreach ($sheets as $i_sht => $rows) {
            $comp_lvl_ids = $rows[0];
            $comp_lvl_ids = array_filter($comp_lvl_ids, fn($comp_lvl_i, $i_comp_lvl) => $i_comp_lvl >= 3 && $comp_lvl_i, ARRAY_FILTER_USE_BOTH);
            $target_col_index = array_keys($comp_lvl_ids);
            $comp_lvl_ids = array_values($comp_lvl_ids);
            $rows = array_filter($rows, fn($row_i, $i_row) => $i_row >= 3 && $row_i[0], ARRAY_FILTER_USE_BOTH);
            $comp_lvl_targets = $this->db->get('comp_lvl_target')->result_array();
            foreach ($rows as $i_row => $row_i) {
                $area_lvl_pstn_ids = explode(",", $row_i[0]);
                foreach ($area_lvl_pstn_ids as $area_lvl_pstn_id) {
                    $data['area_lvl_pstn_id'] = $area_lvl_pstn_id;
                    foreach ($comp_lvl_ids as $i_comp_lvl => $comp_lvl_i) {
                        $data['comp_lvl_id'] = $comp_lvl_i;
                        $target = $row_i[$target_col_index[$i_comp_lvl]];
                        // $data['target'] = $target ? $target : 0;
                        if ($target) {
                            $data['target'] = $target;
                            $existing = array_filter($comp_lvl_targets, fn($c_l_target_i, $i_c_p_target) => $c_l_target_i['area_lvl_pstn_id'] == $area_lvl_pstn_id && $c_l_target_i['comp_lvl_id'] == $comp_lvl_i, ARRAY_FILTER_USE_BOTH);
                            if ($existing) {
                                $existing = array_values($existing)[0];
                                $data['id'] = $existing['id'];
                                $data_updates[] = $data;
                            } else {
                                unset($data['id']);
                                $data_inserts[] = $data;
                            }
                        }
                    }
                }
            }
        }
        echo '<pre>', var_dump("INSERT:");
        // echo '<pre>', var_dump($data_inserts);
        if ($data_inserts) echo $this->db->insert_batch('comp_lvl_target', $data_inserts);
        echo '<pre>', var_dump("UPDATE:");
        // echo '<pre>', var_dump($data_updates);
        if ($data_updates) echo $this->db->update_batch('comp_lvl_target', $data_updates, 'id');
        die;
    }

    public function import_lvl_dictionary()
    {
        $this->load->helper('conversion');
        $this->load->helper('extract_spreadsheet');
        // $sheets = extract_spreadsheet('./uploads/imports_admin/level_dictionary_competency_1.xlsx');
        $sheets = extract_spreadsheet('./uploads/imports_admin/level_dictionary_competency_2.xlsx');
        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $comp_lvl = array_column($comp_lvl, null, 'code');

        $data_update = [];
        // $sheet_names = ['ADT', 'ENG', 'INF', 'COR', 'INT', 'TEN', 'LDO', 'REO', 'WMA', 'CMA', 'DEM', 'POR', 'CHM', 'EMO', 'SBO'];
        $sheet_names = [
            'ACH',
            'CTH',
            'DEV',
            'PRO',
            'BIN',
            'CFO',
            'INO',
            'PSO',
            'TCO',
            'RBU',
        ];
        foreach ($sheet_names as $sheet => $code) {
            $data = [
                'id' => $comp_lvl[$code]['id'],
                'definisi' => $sheets[$sheet][5][1],
                'keterangan' => $sheets[$sheet][7][1],
                'dimension_1' => $sheets[$sheet][11][1],
                'indicator_1_1_t' => $sheets[$sheet][11][2],
                'indicator_1_2_t' => $sheets[$sheet][11][3],
                'indicator_1_3_t' => $sheets[$sheet][11][4],
                'indicator_1_4_t' => $sheets[$sheet][11][5],
                'indicator_1_5_t' => $sheets[$sheet][11][5],
                'indicator_1_6_t' => $sheets[$sheet][11][7],
                'indicator_1_1_b' => $sheets[$sheet][12][2],
                'indicator_1_2_b' => $sheets[$sheet][12][3],
                'indicator_1_3_b' => $sheets[$sheet][12][4],
                'indicator_1_4_b' => $sheets[$sheet][12][5],
                'indicator_1_5_b' => $sheets[$sheet][12][5],
                'indicator_1_6_b' => $sheets[$sheet][12][7],
                'dimension_2' => $sheets[$sheet][13][1],
                'indicator_2_1_t' => $sheets[$sheet][13][2],
                'indicator_2_2_t' => $sheets[$sheet][13][3],
                'indicator_2_3_t' => $sheets[$sheet][13][4],
                'indicator_2_4_t' => $sheets[$sheet][13][5],
                'indicator_2_5_t' => $sheets[$sheet][13][5],
                'indicator_2_6_t' => $sheets[$sheet][13][7],
                'indicator_2_1_b' => $sheets[$sheet][14][2],
                'indicator_2_2_b' => $sheets[$sheet][14][3],
                'indicator_2_3_b' => $sheets[$sheet][14][4],
                'indicator_2_4_b' => $sheets[$sheet][14][5],
                'indicator_2_5_b' => $sheets[$sheet][14][5],
                'indicator_2_6_b' => $sheets[$sheet][14][7],
            ];
            if (isset($sheets[$sheet][15])) {
                $data = array_merge($data, array(
                    'dimension_3' => $sheets[$sheet][15][1],
                    'indicator_3_1_t' => $sheets[$sheet][15][2],
                    'indicator_3_2_t' => $sheets[$sheet][15][3],
                    'indicator_3_3_t' => $sheets[$sheet][15][4],
                    'indicator_3_4_t' => $sheets[$sheet][15][5],
                    'indicator_3_5_t' => $sheets[$sheet][15][5],
                    'indicator_3_6_t' => $sheets[$sheet][15][7],
                    'indicator_3_1_b' => $sheets[$sheet][16][2],
                    'indicator_3_2_b' => $sheets[$sheet][16][3],
                    'indicator_3_3_b' => $sheets[$sheet][16][4],
                    'indicator_3_4_b' => $sheets[$sheet][16][5],
                    'indicator_3_5_b' => $sheets[$sheet][16][5],
                    'indicator_3_6_b' => $sheets[$sheet][16][7],
                ));
            }
            $data_update[] = $data;
        }
        // echo '<pre>', var_dump($data_update);
        // die;
        if ($data_update) echo $this->db->update_batch('comp_lvl', $data_update, 'id');
    }
}
