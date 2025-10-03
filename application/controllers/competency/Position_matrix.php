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
        $this->load->model('competency/m_comp_position_score', 'm_c_p_score'); // <-- ADD: ambil actual score (latest)
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
        $this->load->model('employee/m_employee', 'm_emp'); // <-- ADD: daftar pegawai (punya oalp_id)
    }

    public function index()
    {
        $NRP = $this->session->userdata('NRP');
        $matrix_points = [];

        $user = $this->m_user->get_area_lvl_pstn_user($NRP, 'NRP', false);
        if ($user) {
            $user_oalp_md5 = md5($user['area_lvl_pstn_id']);
            $position      = $this->m_pstn->get_area_lvl_pstn($user_oalp_md5, 'md5(oalp.id)', false);

            if ($position) {
                $pos_id     = (int) $position['id'];
                $pos_id_md5 = md5($pos_id);

                $superiors    = array_reverse($this->m_pstn->get_superiors($pos_id_md5));
                $subordinates = $this->m_pstn->get_subordinates($user_oalp_md5);

                $pushMtx = function (&$dst, $mtx, $subs) {
                    $mtx['subordinates'] = $subs;
                    $dst[] = $mtx;
                };

                // cari matrix_point dari atasan
                $superior_mtx = null;
                foreach ($superiors as $s) {
                    if (($s['type'] ?? null) === 'matrix_point' && (int)$s['id'] !== $pos_id) {
                        $superior_mtx = $s; // last match
                    }
                }

                if ($superior_mtx) {
                    if ((int)$superior_mtx['area_id'] === (int)$user['oa_id']) {
                        $pushMtx($matrix_points, $superior_mtx, $this->m_pstn->get_subordinates($pos_id_md5, 'with_without_matrix'));
                    } else {
                        $mtx_from_sup = null;
                        foreach ($superiors as $s) {
                            if (!empty($s['matrix_point'])) {
                                $mtx_from_sup = (int)$s['matrix_point'];
                                break;
                            }
                        }

                        $sub_mtx_rows = [];
                        foreach ($subordinates as $row) {
                            if (!empty($row['matrix_point'])) $sub_mtx_rows[] = $row;
                        }

                        if (!$mtx_from_sup) {
                            $pushMtx($matrix_points, $superior_mtx, $this->m_pstn->get_subordinates($pos_id_md5, 'without_matrix'));

                            $ids = [];
                            foreach ($sub_mtx_rows as $r) $ids[(int)$r['matrix_point']] = true;
                            if ($ids) {
                                $mps   = $this->db->where_in('id', array_keys($ids))->get('org_area_lvl_pstn')->result_array();
                                $group = [];
                                foreach ($sub_mtx_rows as $r) {
                                    $group[(int)$r['matrix_point']][] = $r;
                                }
                                foreach ($mps as $mp) {
                                    $subs = [];
                                    foreach ($group[(int)$mp['id']] ?? [] as $sb) {
                                        $subs = array_merge($subs, $this->m_pstn->get_subordinates(md5((int)$sb['id'])));
                                    }
                                    $pushMtx($matrix_points, $mp, $subs);
                                }
                            }
                        } else {
                            $mtxp = $this->db->get_where('org_area_lvl_pstn', ['id' => $mtx_from_sup])->row_array();
                            $pushMtx($matrix_points, $mtxp, $this->m_pstn->get_subordinates($pos_id_md5));
                        }
                    }
                } else {
                    // tidak ada matrix_point dari atasan
                    foreach ($subordinates as $row) {
                        if (($row['type'] ?? null) === 'matrix_point') {
                            $row['subordinates'] = $this->m_pstn->get_subordinates(md5((int)$row['id']), 'with_without_matrix');
                            $matrix_points[] = $row;
                        }
                    }
                }
            }
        }

        // Seleksi matrix_point jika jumlah banyak
        if (count($matrix_points) > 3 && !$this->input->post('matrix_points')) {
            $this->matrix_points_select($matrix_points);
            return;
        }

        if ($this->input->post('matrix_points')) {
            $matrix_points_base = array_column($matrix_points, null, 'id');
            $matrix_points = [];
            foreach ($this->input->post('matrix_points') as $i_mp => $mp_i) {
                if (isset($matrix_points_base[$mp_i])) $matrix_points[] = $matrix_points_base[$mp_i];
            }
        }

        // --- Data inti untuk matrix per PEGAWAI (Plan/Actual/Gap) ---
        $competencies = $this->m_c_pstn->get_comp_position();              // daftar positional competencies
        $targets      = $this->m_c_p_targ->get_comp_position_target();     // plan per (comp_pstn_id x area_lvl_pstn_id)
        $scores       = $this->m_c_p_score->get_cp_score(null, null, true); // actual (latest per NRP x comp_pstn_id)

        // grup kompetensi per matrix_point (sudah ada helpernya)
        $data['competencies'] = $this->group_competencies($matrix_points, $competencies);

        // Ambil seluruh pegawai yang punya mapping OALP, lalu pecah per matrix_point: hanya subordinates di MP tsb
        $employees_all = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');

        // Build: employees_by_mp[mpId] = list pegawai subordinate mp tsb (dengan field plan/actual/gap per comp_pstn)
        $data['employees_by_mp'] = [];
        foreach ($matrix_points as $mp) {
            $mpId     = (int)$mp['id'];
            $sub_oalp = array_map(fn($p) => (int)$p['id'], $mp['subordinates'] ?? []);
            $emp_sub  = array_values(array_filter($employees_all, fn($e) => isset($e['oalp_id']) && in_array((int)$e['oalp_id'], $sub_oalp, true)));

            $mp_comp_positions = $data['competencies'][$mpId] ?? []; // list comp_pstn milik MP ini
            $data['employees_by_mp'][$mpId] = $this->create_employee_matrix_position(
                $emp_sub,             // pegawai di MP ini
                $mp_comp_positions,   // daftar comp_pstn di MP ini
                $scores,              // actual
                $targets              // plan
            );
        }

        // Filter dict berdasarkan matrix_points (dipertahankan seperti semula)
        $comp_pstn_dicts = $this->db->query("
            SELECT * FROM comp_pstn_dict cpd
            LEFT JOIN comp_position cp ON cp.id = cpd.comp_pstn_id
        ")->result_array();
        $matrix_point_ids = array_column($matrix_points, 'id');
        $data['comp_pstn_dicts'] = array_filter($comp_pstn_dicts, fn($cpd) => in_array($cpd['area_lvl_pstn_id'], $matrix_point_ids));

        $data['admin'] = false;
        $data['matrix_position_active'] = $this->input->get('matrix_position_active');
        $data['matrix_points'] = $matrix_points;

        // View TETAP: position_matrix.php (sekarang menampilkan baris pegawai)
        $data['content'] = "competency/position_matrix_user";
        $this->load->view('templates/header_footer', $data);
    }

    public function matrix_points_select($matrix_points)
    {
        $data['admin'] = false;
        $data['matrix_points'] = $matrix_points;
        $data['content'] = "competency/matrix_points_select";
        $this->load->view('templates/header_footer', $data);
    }

    // (dipertahankan) – untuk kebutuhan lain di halaman pengaturan target
    public function create_matrix($matrix_points, $competencies, $targets)
    {
        foreach ($matrix_points as $i_mtxp => $mtxp_i) {
            foreach ($mtxp_i['subordinates'] as $i_oalp => $oalp_i) {
                foreach ($competencies as $i_cp => $cp_i) {
                    $matrix_points[$i_mtxp]['subordinates'][$i_oalp]['target'][$cp_i['id']] = 0;
                    $target = array_filter($targets, fn($cpt_i, $i_cpt) => $cpt_i['comp_pstn_id'] == $cp_i['id'] && $cpt_i['cpt_oalp_id'] == $oalp_i['id'], ARRAY_FILTER_USE_BOTH);
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
            $comp_positions[$mtxp_i['id']] = array_values(array_filter(
                $competencies,
                fn($cp_i) => $cp_i['area_lvl_pstn_id'] == $mtxp_i['id']
            ));
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

    /**
     * NEW: bentuk matrix Plan/Actual/Gap untuk baris PEGAWAI pada sebuah matrix_point
     * - Plan  : dari comp_pstn_target (cpt.area_lvl_pstn_id = oalp_id pegawai)
     * - Actual: dari comp_pstn_score latest per NRP x comp_pstn_id
     */
    private function create_employee_matrix_position(array $employees, array $comp_positions, array $cp_scores, array $cp_targets): array
    {
        // Actual: scoreMap[NRP][comp_pstn_id] = score
        $scoreMap = [];
        foreach ($cp_scores as $s) {
            $nrp   = $s['NRP'];
            $cpid  = (int)$s['comp_pstn_id'];
            if (!isset($scoreMap[$nrp])) $scoreMap[$nrp] = [];
            $scoreMap[$nrp][$cpid] = is_null($s['score']) ? null : (float)$s['score'];
        }

        // Plan: targetMap[oalp_id][comp_pstn_id] = target
        $targetMap = [];
        foreach ($cp_targets as $t) {
            $oalp = (int)$t['cpt_oalp_id'];   // target berlaku di posisi pegawai ini
            $cpid = (int)$t['comp_pstn_id'];
            if (!isset($targetMap[$oalp])) $targetMap[$oalp] = [];
            $targetMap[$oalp][$cpid] = is_null($t['target']) ? null : (float)$t['target'];
        }

        $cpIds = array_map(fn($c) => (int)$c['id'], $comp_positions);

        foreach ($employees as &$e) {
            $nrp  = $e['NRP'];
            $oalp = isset($e['oalp_id']) ? (int)$e['oalp_id'] : null;

            $e['cp_plan']   = [];
            $e['cp_actual'] = [];
            $e['cp_gap']    = [];

            foreach ($cpIds as $cpid) {
                $plan   = ($oalp !== null && isset($targetMap[$oalp])) ? ($targetMap[$oalp][$cpid] ?? null) : null;
                $actual = $scoreMap[$nrp][$cpid] ?? null;
                $gap    = (is_numeric($plan) && is_numeric($actual)) ? ($actual - $plan) : null;

                $e['cp_plan'][$cpid]   = $plan;
                $e['cp_actual'][$cpid] = $actual;
                $e['cp_gap'][$cpid]    = $gap;
            }
        }
        unset($e);

        return $employees;
    }
}
