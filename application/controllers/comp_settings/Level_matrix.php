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
        $this->load->model('organization/m_level', 'm_lvl');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
    }

    public function index()
    {
        // --- Data dasar
        $positions     = $this->m_pstn->get_subordinates(md5(1));
        $competencies  = $this->m_c_lvl->get_comp_level();
        $targets       = $this->m_c_l_targ->get_comp_level_target();

        $data = [
            'admin'         => true,
            'level_active'  => $this->input->get('level_active', true), // XSS sanitize
            'comp_levels'   => $competencies,
            'area_pstns'    => [],
            'area_lvls'     => [],
            'content'       => 'competency/level_matrix',
        ];

        // --- Rakit matriks target per posisi (FAST)
        $data['area_pstns'] = $this->create_matrix($positions, $competencies, $targets);

        // --- Hitung area_lvls (yang menjadi TAB): ambil yang 'equals' kosong/tidak ada
        //     & key-kan by 'oal_id' (unique), tanpa array_column/array_values berulang.
        $area_lvls_map = [];
        foreach ($data['area_pstns'] as $row) {
            $equals_empty = empty($row['equals']); // aman untuk key yang tidak ada
            if ($equals_empty) {
                // sesuaikan kunci id level area (oal_id jika ada; fallback id)
                $oal_key = isset($row['oal_id']) ? (int)$row['oal_id'] : (int)($row['id'] ?? 0);
                if ($oal_key) {
                    $area_lvls_map[$oal_key] = $row; // overwrite-safe, hasil tetap unik
                }
            }
        }
        $data['area_lvls'] = array_values($area_lvls_map);

        // --- Render
        $this->load->view('templates/header_footer', $data);
    }

    /**
     * OPTIMIZED:
     * - Buat index targetMap[oalp_id][comp_lvl_id] = target (O(T))
     * - Pre-fill default 0 untuk semua competency id (sekali, O(C))
     * - Assign ke setiap posisi (O(P + isi_target_posisi))
     * Total: O(P + C + T), tanpa array_filter di inner-loop.
     */
    public function create_matrix(array $positions, array $competencies, array $targets): array
    {
        // 1) Index target
        $targetMap = [];
        foreach ($targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            // target null -> anggap 0 sesuai perilaku lama
            $targetMap[$oalp][$clid] = is_null($t['target']) ? 0.0 : (float)$t['target'];
        }

        // 2) Default target 0 untuk semua competency id
        $default = [];
        foreach ($competencies as $c) {
            $default[(int)$c['id']] = 0.0;
        }

        // 3) Assign ke tiap posisi (hindari copy berlebihan)
        foreach ($positions as &$p) {
            // Sesuaikan key id OALP posisi: biasanya 'id' (OALP id). Jika struktur beda, sesuaikan.
            $oalp_id = (int)($p['id'] ?? $p['oal_id'] ?? 0);
            $p['target'] = $default; // salin default cepat
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
        // Pertahankan param level_active (GET) bila ada
        $level_active = $this->input->get('level_active', true);
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

    public function comp_lvl($action, $hash_id = null, $hash_id2 = null)
    {
        // jaga-jaga: pertahankan level_active dari GET/POST
        $level_active = $this->input->get('level_active', true);

        if (!$level_active && $this->input->post('hash_area_lvl_id')) {
            $area_lvl = $this->m_lvl->get_area_lvl($this->input->post('hash_area_lvl_id'), 'md5(oal.id)', false);
            if ($area_lvl && isset($area_lvl['id'])) {
                $level_active = md5($area_lvl['id']);
            }
        }
        $suffix = $level_active ? ('?level_active=' . $level_active) : '';

        switch ($action) {
            case 'add':
                flash_swal('error', 'Target add Failed');
                $ok = $this->m_c_lvl->add();
                if ($ok) flash_swal('success', 'Target added Successfully');
                break;

            case 'edit':
                flash_swal('error', 'Target edit Failed');
                $ok = $this->m_c_lvl->edit();
                if ($ok) flash_swal('success', 'Target edited Successfully');
                break;

            case 'delete':
                flash_swal('error', 'Competency Delete Failed');
                $ok = $this->m_c_lvl->delete($hash_id);
                if ($ok) flash_swal('success', 'Competency Deleted Successfully');
                break;

            default:
                show_404();
        }

        redirect('comp_settings/level_matrix' . $suffix);
    }
}
