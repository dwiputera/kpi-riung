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
        $positions    = $this->m_pstn->get_subordinates(md5(1));
        $competencies = $this->m_c_lvl->get_comp_level();
        $targets      = $this->m_c_l_targ->get_comp_level_target();

        $data = [
            'admin'         => true,
            'level_active'  => $this->input->get('level_active', true),
            'comp_levels'   => $competencies,
            'area_pstns'    => [],
            'area_lvls'     => [],
            'content'       => 'competency/level_matrix',
        ];

        $data['area_pstns'] = $this->create_matrix($positions, $competencies, $targets);

        $area_lvls_map = [];
        foreach ($data['area_pstns'] as $row) {
            $equals_empty = empty($row['equals']);
            if ($equals_empty) {
                $oal_key = isset($row['oal_id']) ? (int)$row['oal_id'] : (int)($row['id'] ?? 0);
                if ($oal_key) {
                    $area_lvls_map[$oal_key] = $row;
                }
            }
        }
        $data['area_lvls'] = array_values($area_lvls_map);

        $this->load->view('templates/header_footer', $data);
    }

    public function dictionary()
    {
        $data = [
            'admin'        => true,
            'dictionaries' => $this->m_c_lvl->get_comp_level(),
            'content'      => 'competency/level_dictionary',
        ];

        $this->load->view('templates/header_footer', $data);
    }

    public function create_matrix(array $positions, array $competencies, array $targets): array
    {
        $targetMap = [];
        foreach ($targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            $targetMap[$oalp][$clid] = is_null($t['target']) ? 0.0 : (float)$t['target'];
        }

        $default = [];
        foreach ($competencies as $c) {
            $default[(int)$c['id']] = 0.0;
        }

        foreach ($positions as &$p) {
            $oalp_id = (int)($p['id'] ?? $p['oal_id'] ?? 0);
            $p['target'] = $default;

            if ($oalp_id && isset($targetMap[$oalp_id])) {
                foreach ($targetMap[$oalp_id] as $clid => $val) {
                    $p['target'][$clid] = $val;
                }
            }
        }
        unset($p);

        return $positions;
    }

    public function comp_lvl_target($action)
    {
        $level_active = $this->input->post('level_active', true) ?: $this->input->get('level_active', true);
        $suffix = $level_active ? ('?level_active=' . $level_active) : '';

        switch ($action) {
            case 'submit':
                $success = $this->m_c_l_targ->submit();

                $this->session->set_flashdata('swal', [
                    'type'    => $success ? 'success' : 'error',
                    'message' => $success ? 'Target Score Submitted Successfully' : 'Target Score Submit Failed',
                ]);

                redirect('comp_settings/level_matrix' . $suffix);
                break;

            default:
                show_404();
        }
    }

    public function comp_lvl($action, $hash_id = null)
    {
        switch ($action) {
            case 'add':
                $ok = $this->m_c_lvl->add();

                $this->session->set_flashdata('swal', [
                    'type'    => $ok ? 'success' : 'error',
                    'message' => $ok ? 'Competency Added Successfully' : 'Competency Add Failed',
                ]);
                break;

            case 'edit':
                $ok = $this->m_c_lvl->edit();

                $this->session->set_flashdata('swal', [
                    'type'    => $ok ? 'success' : 'error',
                    'message' => $ok ? 'Competency Edited Successfully' : 'Competency Edit Failed',
                ]);
                break;

            case 'delete':
                $ok = $this->m_c_lvl->delete($hash_id);

                $this->session->set_flashdata('swal', [
                    'type'    => $ok ? 'success' : 'error',
                    'message' => $ok ? 'Competency Deleted Successfully' : 'Competency Delete Failed',
                ]);
                break;

            default:
                show_404();
        }

        redirect('comp_settings/level_matrix/dictionary');
    }
}
