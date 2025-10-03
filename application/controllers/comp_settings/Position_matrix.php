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

    public function index($position_id = 1)
    {
        // --- data dasar
        $posId   = (int)$position_id;
        $posMd5  = md5($posId);
        $position = $this->m_pstn->get_area_lvl_pstn($posMd5, 'md5(oalp.id)', false);

        $subordinates = $this->m_pstn->get_subordinates($posMd5);
        $superiors    = array_reverse($this->m_pstn->get_superiors(md5((int)$position['id'])));

        // --- tentukan daftar matrix points (atasan jika ada, kalau tidak pakai yang dari bawahan)
        $matrix_points = [];

        // cari atasan bertipe matrix_point (ambil yang pertama ditemukan setelah dibalik)
        $superior_mtxp = null;
        foreach ($superiors as $sup) {
            if (($sup['type'] ?? null) === 'matrix_point' && (int)$sup['id'] !== (int)$position['id']) {
                $superior_mtxp = $sup;
                break;
            }
        }

        if ($superior_mtxp) {
            $mtxp = $superior_mtxp;
            $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5((int)$position['id']));
            $matrix_points[] = $mtxp;
        } else {
            // ambil matrix point dari bawahan
            foreach ($subordinates as $sub) {
                if (($sub['type'] ?? null) === 'matrix_point') {
                    $mtxp = $sub;
                    // with_without_matrix sesuai perilaku lama
                    $mtxp['subordinates'] = $this->m_pstn->get_subordinates(md5((int)$sub['id']), 'with_without_matrix');
                    $matrix_points[] = $mtxp;
                }
            }
        }

        // --- bila matrix point > 3 dan belum dipilih, tampilkan halaman seleksi
        if (count($matrix_points) > 3 && !$this->input->post('matrix_points')) {
            $this->matrix_points_select($matrix_points);
            return;
        }

        // --- jika ada pilihan post matrix_points, filter sesuai pilihan
        if ($this->input->post('matrix_points')) {
            $chosen_ids = $this->input->post('matrix_points'); // array of id
            $byId = [];
            foreach ($matrix_points as $mp) $byId[(int)$mp['id']] = $mp;

            $filtered = [];
            foreach ($chosen_ids as $id) {
                $id = (int)$id;
                if (isset($byId[$id])) $filtered[] = $byId[$id];
            }
            $matrix_points = $filtered;
            $data['matrix_points_chosen'] = $chosen_ids;
        }

        // --- ambil kompetensi & target
        $competencies = $this->m_c_pstn->get_comp_position();
        $targets      = $this->m_c_p_targ->get_comp_position_target();

        // --- rakit matrix target per subordinate
        $data['matrix_points'] = $this->create_matrix($matrix_points, $competencies, $targets);

        // --- kelompokkan kompetensi per matrix point (cepat: pakai index)
        $data['competencies'] = $this->group_competencies($matrix_points, $competencies);

        // --- view
        $data['admin'] = true;
        $data['matrix_position_active'] = $this->input->get('matrix_position_active', true);
        $data['content'] = "competency/position_matrix";
        $this->load->view('templates/header_footer', $data);
    }

    public function matrix_points_select($matrix_points)
    {
        $data['admin'] = true;
        $data['matrix_points'] = $matrix_points;
        $data['content'] = "competency/matrix_points_select";
        $this->load->view('templates/header_footer', $data);
    }

    /**
     * OPTIMIZED:
     * Build index targetMap[oalp_id][comp_pstn_id] = target, lalu merge cepat.
     * Hindari array_filter di inner loop.
     */
    public function create_matrix(array $matrix_points, array $competencies, array $targets): array
    {
        // 1) index target: targetMap[oalp_id][comp_pstn_id] = target
        $targetMap = [];
        foreach ($targets as $t) {
            $oalp = (int)$t['cpt_oalp_id'];
            $cpid = (int)$t['comp_pstn_id'];
            $targetMap[$oalp][$cpid] = is_null($t['target']) ? 0.0 : (float)$t['target'];
        }

        // 2) default 0 untuk semua comp_position id
        $default = [];
        foreach ($competencies as $cp) {
            $default[(int)$cp['id']] = 0.0;
        }

        // 3) assign ke tiap subordinate di setiap matrix point
        foreach ($matrix_points as &$mtxp) {
            if (!isset($mtxp['subordinates']) || !is_array($mtxp['subordinates'])) continue;

            foreach ($mtxp['subordinates'] as &$oalp) {
                $oalp_id = (int)$oalp['id'];
                // set default
                $oalp['target'] = $default;

                if (isset($targetMap[$oalp_id])) {
                    foreach ($targetMap[$oalp_id] as $cpid => $val) {
                        $oalp['target'][$cpid] = $val;
                    }
                }
            }
            unset($oalp);
        }
        unset($mtxp);

        return $matrix_points;
    }

    /**
     * OPTIMIZED:
     * Kelompokkan kompetensi per matrix point via pre-index by area_lvl_pstn_id
     * comp_positions_map[oalp_id][] = row
     */
    public function group_competencies(array $matrix_points, array $competencies): array
    {
        // index kompetensi per oalp id
        $compMap = [];
        foreach ($competencies as $cp) {
            $oalp_id = (int)$cp['area_lvl_pstn_id'];
            $compMap[$oalp_id][] = $cp;
        }

        $out = [];
        foreach ($matrix_points as $mtxp) {
            $id = (int)$mtxp['id'];
            $out[$id] = $compMap[$id] ?? [];
        }
        return $out;
    }

    public function comp_pstn_target($action)
    {
        $mp_active = $this->input->get('matrix_position_active', true);
        $suffix    = $mp_active ? ('?matrix_position_active=' . $mp_active) : '';

        switch ($action) {
            case 'submit':
                flash_swal('error', 'Target Score Submit Failed');
                $success = $this->m_c_p_targ->submit();
                if ($success) flash_swal('success', 'Target Score Submitted Successfully');
                redirect('comp_settings/position_matrix' . $suffix);
                break;

            default:
                show_404();
        }
    }

    public function comp_pstn($action, $hash_id = null, $hash_id2 = null)
    {
        $mp_active = $this->input->get('matrix_position_active', true);
        if (!$mp_active) {
            if ($this->input->post('hash_area_lvl_pstn_id')) {
                $mp_active = $this->input->post('hash_area_lvl_pstn_id');
            } elseif ($this->input->post('hash_comp_pstn_id')) {
                $comp_pstn = $this->m_c_pstn->get_comp_position($this->input->post('hash_area_lvl_pstn_id'), 'md5(id)', false);
                if ($comp_pstn && isset($comp_pstn['area_lvl_pstn_id'])) {
                    $mp_active = md5((int)$comp_pstn['area_lvl_pstn_id']);
                }
            }
        }
        $suffix = $mp_active ? ('?matrix_position_active=' . $mp_active) : '';

        switch ($action) {
            case 'add':
                flash_swal('error', 'Target add Failed');
                $ok = $this->m_c_pstn->add();
                if ($ok) flash_swal('success', 'Target added Successfully');
                break;

            case 'edit':
                flash_swal('error', 'Target edit Failed');
                $ok = $this->m_c_pstn->edit();
                if ($ok) flash_swal('success', 'Target edited Successfully');
                break;

            case 'delete':
                flash_swal('error', 'Competency Delete Failed');
                $ok = $this->m_c_pstn->delete($hash_id);
                if ($ok) flash_swal('success', 'Competency Deleted Successfully');
                break;

            default:
                show_404();
        }

        redirect('comp_settings/position_matrix' . $suffix);
    }

    public function dictionary($hash_pstn_id)
    {
        $data['position']     = $this->m_pstn->get_area_lvl_pstn($hash_pstn_id, 'md5(oalp.id)', false);
        $data['dictionaries'] = $this->m_c_pstn->get_comp_position($hash_pstn_id, 'md5(area_lvl_pstn_id)');
        $data['admin']        = true;
        $data['content']      = "competency/position_dictionary";
        $this->load->view('templates/header_footer', $data);
    }

    public function dictionary_edit()
    {
        $data['position']     = $this->m_pstn->get_area_lvl_pstn();
        $data['dictionaries'] = $this->m_c_pstn->get_comp_position();
        $data['content']      = "competency/position_dictionary_edit";
        $this->load->view('templates/header_footer', $data);
    }

    public function dictionary_submit()
    {
        flash_swal('error', 'Dictionary Update Failed');
        $success = $this->m_c_pstn->dictionary_submit();
        if ($success) flash_swal('success', 'Dictionary Updated Successfully');
        redirect('comp_settings/position_matrix/dictionary_edit');
    }
}
