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
        $this->load->model('competency/m_comp_level_score', 'm_c_l_s'); // ambil Actual Score (latest)
        $this->load->model('organization/m_level', 'm_lvl');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
        $this->load->model('employee/m_employee', 'm_emp'); // ambil daftar pegawai
    }

    public function index()
    {
        $NRP  = $this->session->userdata('NRP');
        $user = $this->m_user->get_area_lvl_pstn_user($NRP, 'NRP', false);

        $data = [
            'area_pstns'  => [],
            'area_lvls'   => [],
            'comp_levels' => [],
            'employees'   => [],
            'admin'       => false,
            'level_active' => $this->input->get('level_active'),
        ];

        if ($user) {
            // 1) Ambil struktur bawahan (subordinates) milik user untuk basis Tab Level
            $positions    = $this->m_pstn->get_subordinates(md5($user['area_lvl_pstn_id']), 'with_matrix');
            $competencies = $this->m_c_lvl->get_comp_level();            // daftar competency
            $targets      = $this->m_c_l_targ->get_comp_level_target();  // target per posisi
            $scores       = $this->m_c_l_s->get_cl_score(null, null, true); // actual (latest per NRP+comp)

            // 2) Siapkan daftar Level (tab) seperti sebelumnya
            $data['area_pstns'] = $this->create_matrix($positions, $competencies, $targets);
            $ids = array_keys(array_column($data['area_pstns'], null, 'oal_id'));
            $area_levels = array_values(array_filter($data['area_pstns'], function ($lvl) use ($ids) {
                return empty($lvl['equals']) || !in_array($lvl['equals'], $ids);
            }));
            $data['area_lvls']   = array_values(array_column($area_levels, null, 'oal_id'));
            $data['comp_levels'] = $competencies;

            // 3) Ambil pegawai lalu FILTER hanya yang termasuk ke dalam subordinates (berdasar oalp.id)
            $employees_all = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id'); // semua yg punya mapping OALP
            $employees_sub = $this->filter_subordinate_employees($employees_all, $positions);

            // 4) Bentuk matrix pegawai: Plan (target), Actual (score), dan Gap per competency
            $data['employees'] = $this->create_employee_matrix($employees_sub, $competencies, $scores, $targets);
        }

        // View baru: tabel per-pegawai (Plan/Actual/Gap) dipisah tab per Level
        $data['content'] = "competency/level_matrix";
        $this->load->view('templates/header_footer', $data);
    }

    /**
     * FUNGSI LAMA - dipakai untuk merakit target per posisi (jangan dihapus)
     */
    public function create_matrix($positions, $competencies, $targets)
    {
        foreach ($positions as $i_oalp => $oalp_i) {
            foreach ($competencies as $i_cl => $cl_i) {
                $positions[$i_oalp]['target'][$cl_i['id']] = 0;
                $target = array_filter(
                    $targets,
                    fn($clt_i, $i_clt) => $clt_i['comp_lvl_id'] == $cl_i['id'] && $clt_i['area_lvl_pstn_id'] == $oalp_i['id'],
                    ARRAY_FILTER_USE_BOTH
                );
                $target = array_values($target);
                if ($target) $positions[$i_oalp]['target'][$cl_i['id']] = $target[0]['target'];
            }
        }
        return $positions;
    }

    /**
     * NEW: filter pegawai agar hanya yang termasuk subordinates (berdasar OALP id)
     */
    private function filter_subordinate_employees(array $employees, array $positions): array
    {
        $sub_oalp_ids = array_map(fn($p) => (int)$p['id'], $positions);
        return array_values(array_filter($employees, function ($e) use ($sub_oalp_ids) {
            return isset($e['oalp_id']) && in_array((int)$e['oalp_id'], $sub_oalp_ids, true);
        }));
    }

    /**
     * NEW: bentuk matrix Plan/Actual/Gap per competency untuk baris pegawai
     */
    private function create_employee_matrix(array $employees, array $comp_lvl, array $cl_scores, array $cl_targets): array
    {
        // Map Actual: scoreMap[NRP][comp_lvl_id] = score
        $scoreMap = [];
        foreach ($cl_scores as $s) {
            $nrp  = $s['NRP'];
            $clid = (int)$s['comp_lvl_id'];
            if (!isset($scoreMap[$nrp])) $scoreMap[$nrp] = [];
            $scoreMap[$nrp][$clid] = is_null($s['clas_score']) ? null : (float)$s['clas_score'];
        }

        // Map Plan: targetMap[oalp_id][comp_lvl_id] = target
        $targetMap = [];
        foreach ($cl_targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            if (!isset($targetMap[$oalp])) $targetMap[$oalp] = [];
            $targetMap[$oalp][$clid] = is_null($t['target']) ? null : (float)$t['target'];
        }

        $compLvlIds = array_map(fn($c) => (int)$c['id'], $comp_lvl);

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
