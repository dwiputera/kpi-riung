<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Level_matrix extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // Models
        $this->load->model('competency/m_comp_level', 'm_c_lvl');
        $this->load->model('competency/m_comp_level_target', 'm_c_l_targ');
        $this->load->model('competency/m_comp_level_score', 'm_c_l_s'); // Actual Score (latest)
        $this->load->model('organization/m_level', 'm_lvl');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
        $this->load->model('employee/m_employee', 'm_emp'); // daftar pegawai
    }

    public function index()
    {
        $NRP  = $this->session->userdata('NRP');
        $user = $this->m_user->get_area_lvl_pstn_user($NRP, 'NRP', false);

        $data = [
            'area_pstns'   => [],
            'area_lvls'    => [],
            'comp_levels'  => [],
            'employees'    => [],
            'admin'        => false,
            'level_active' => $this->input->get('level_active', true),
        ];

        if ($user) {
            // 1) Data dasar sekali
            $user_oalp_md5 = md5($user['area_lvl_pstn_id']);
            $positions     = $this->m_pstn->get_subordinates($user_oalp_md5, 'with_matrix');   // basis tab Level
            $competencies  = $this->m_c_lvl->get_comp_level();                                  // daftar competency
            $targets       = $this->m_c_l_targ->get_comp_level_target();                        // target per posisi
            $scores        = $this->m_c_l_s->get_cl_score(null, null, true);                    // actual (latest per NRP+comp)

            // 2) Matrik target per posisi (FAST)
            $data['area_pstns']  = $this->create_matrix($positions, $competencies, $targets);
            $data['comp_levels'] = $competencies;

            // Set OALP id untuk lookup O(1)
            $oalp_ids = [];
            foreach ($data['area_pstns'] as $row) {
                $oal_id = isset($row['oal_id']) ? (int)$row['oal_id'] : (int)$row['id'];
                $oalp_ids[$oal_id] = true;
            }

            // 3) Tab level = yang bukan "equals" (atau equals tidak ada di set)
            $area_lvls = [];
            foreach ($data['area_pstns'] as $lvl) {
                $eq = isset($lvl['equals']) ? (int)$lvl['equals'] : null;
                if ($eq === null || !isset($oalp_ids[$eq])) {
                    $key = isset($lvl['oal_id']) ? (int)$lvl['oal_id'] : (int)$lvl['id'];
                    $area_lvls[$key] = $lvl; // unique by key
                }
            }
            $data['area_lvls'] = array_values($area_lvls);

            // 4) Pegawai -> filter hanya subordinate (pakai hash-set)
            // (Lebih cepat bila model menyediakan query where_in di DB)
            $employees_all = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');
            $employees_sub = $this->filter_subordinate_employees($employees_all, $positions);

            // 5) Matriks Plan/Actual/Gap per competency
            $data['employees'] = $this->create_employee_matrix($employees_sub, $competencies, $scores, $targets);
        }

        // View
        $data['content'] = "competency/level_matrix_user";
        $this->load->view('templates/header_footer', $data);
    }

    /**
     * OPTIMIZED: rakit target per posisi TANPA array_filter berulang.
     * Build index: targetMap[oalp_id][comp_id] = target, lalu merge cepat.
     */
    public function create_matrix(array $positions, array $competencies, array $targets): array
    {
        // targetMap[oalp_id][comp_lvl_id] = target
        $targetMap = [];
        foreach ($targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            $targetMap[$oalp][$clid] = is_null($t['target']) ? 0.0 : (float)$t['target'];
        }

        // default 0 utk semua competency id
        $default = [];
        foreach ($competencies as $c) {
            $default[(int)$c['id']] = 0.0;
        }

        // assign ke tiap posisi
        foreach ($positions as &$p) {
            $oalp_id = (int)($p['id'] ?? $p['oal_id'] ?? 0);
            $p['target'] = $default; // copy default
            if (isset($targetMap[$oalp_id])) {
                foreach ($targetMap[$oalp_id] as $clid => $val) {
                    $p['target'][$clid] = $val;
                }
            }
        }
        unset($p);

        return $positions;
    }

    /**
     * FAST: filter pegawai agar hanya subordinate (berdasar OALP id) dengan hash-set.
     */
    private function filter_subordinate_employees(array $employees, array $positions): array
    {
        $sub_oalp_set = [];
        foreach ($positions as $p) {
            $sub_oalp_set[(int)$p['id']] = true; // atau 'oal_id' jika itu kunci yang valid
        }

        $out = [];
        foreach ($employees as $e) {
            if (isset($e['oalp_id']) && isset($sub_oalp_set[(int)$e['oalp_id']])) {
                $out[] = $e;
            }
        }
        return $out;
    }

    /**
     * Matriks Plan/Actual/Gap per competency untuk baris pegawai
     */
    private function create_employee_matrix(array $employees, array $comp_lvl, array $cl_scores, array $cl_targets): array
    {
        // Actual: scoreMap[NRP][comp_lvl_id] = score
        $scoreMap = [];
        foreach ($cl_scores as $s) {
            $nrp  = $s['NRP'];
            $clid = (int)$s['comp_lvl_id'];
            if (!isset($scoreMap[$nrp])) $scoreMap[$nrp] = [];
            $scoreMap[$nrp][$clid] = is_null($s['clas_score']) ? null : (float)$s['clas_score'];
        }

        // Plan: targetMap[oalp_id][comp_lvl_id] = target
        $targetMap = [];
        foreach ($cl_targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            if (!isset($targetMap[$oalp])) $targetMap[$oalp] = [];
            $targetMap[$oalp][$clid] = is_null($t['target']) ? null : (float)$t['target'];
        }

        $compLvlIds = [];
        foreach ($comp_lvl as $c) $compLvlIds[] = (int)$c['id'];

        foreach ($employees as &$e) {
            $nrp  = $e['NRP'];
            $oalp = isset($e['oalp_id']) ? (int)$e['oalp_id'] : null;

            $e['cl_target'] = [];
            $e['cl_score']  = [];
            $e['cl_gap']    = [];

            foreach ($compLvlIds as $clid) {
                $plan   = ($oalp !== null && isset($targetMap[$oalp])) ? ($targetMap[$oalp][$clid] ?? null) : null;
                $actual = $scoreMap[$nrp][$clid] ?? null;
                $gap    = (is_numeric($plan) && is_numeric($actual)) ? ($actual - $plan) : null;

                $e['cl_target'][$clid] = $plan;
                $e['cl_score'][$clid]  = $actual;
                $e['cl_gap'][$clid]    = $gap;
            }
        }
        unset($e);

        return $employees;
    }

    public function dictionary()
    {
        $data['dictionaries'] = $this->m_c_lvl->get_comp_level();
        $data['content'] = "competency/level_dictionary";
        $this->load->view('templates/header_footer', $data);
    }
}
