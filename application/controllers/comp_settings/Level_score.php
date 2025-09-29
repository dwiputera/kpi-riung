<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Level_score extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_level_target', 'm_c_l_t');
        $this->load->model('competency/m_comp_level_score', 'm_c_l_s');
        $this->load->model('organization/m_level', 'm_lvl');
        $this->load->model('employee/m_employee', 'm_emp');
    }

    public function index()
    {
        $data['assess_methods'] = $this->db->get('comp_lvl_assess_method')->result_array();
        $data['content'] = "competency/level_score_assess_method";
        $this->load->view('templates/header_footer', $data);
    }

    public function current($am_hash)
    {
        $year = $this->input->get('year') ?? date("Y") + 1;
        $data['year'] = $year;
        $data['assess_method'] = $this->db->get_where('comp_lvl_assess_method', array('md5(id)' => $am_hash))->row_array();
        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $data['comp_lvl'] = $comp_lvl;
        $cl_assess = $this->db->get_where('comp_lvl_assess', array('tahun' => $year, 'md5(method_id)' => $am_hash))->result_array();
        $cl_scores = $this->m_c_l_s->get_cl_score($am_hash, "md5(method_id)");
        $cl_targets = $this->m_c_l_t->get_comp_level_target();
        $employees = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');
        // $employees = $this->m_emp->get_employee(3, 'oal.id');
        $data['employees'] = $this->create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_scores);
        $data['content'] = "competency/level_score";
        $this->load->view('templates/header_footer', $data);
    }

    public function year($am_hash)
    {
        $year = $this->input->get('year') ?? date("Y") + 1;
        $data['year'] = $year;
        $data['assess_method'] = $this->db->get_where('comp_lvl_assess_method', array('md5(id)' => $am_hash))->row_array();
        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $data['comp_lvl'] = $comp_lvl;
        $cl_assess = $this->db->get_where('comp_lvl_assess', array('tahun' => $year, 'md5(method_id)' => $am_hash))->result_array();
        $cl_scores = $this->m_c_l_s->get_cl_score_year($am_hash, "tahun = '$year' AND md5(method_id)");
        $cl_targets = $this->m_c_l_t->get_comp_level_target();
        $employees = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');
        // $employees = $this->m_emp->get_employee(3, 'oal.id');
        $data['employees'] = $this->create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_assess);
        $data['content'] = "competency/level_score_year";
        $this->load->view('templates/header_footer', $data);
    }

    public function year_edit($am_hash)
    {
        $year = $this->input->get('year') ?? date("Y");
        $data['year'] = $year;
        $data['assess_method'] = $this->db->get_where('comp_lvl_assess_method', array('md5(id)' => $am_hash))->row_array();
        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $data['comp_lvl'] = $comp_lvl;
        $cl_assess = $this->db->get_where('comp_lvl_assess', array('tahun' => $year, 'md5(method_id)' => $am_hash))->result_array();
        $cl_scores = $this->m_c_l_s->get_cl_score_year($am_hash, "tahun = '$year' AND md5(method_id)");
        $cl_targets = $this->m_c_l_t->get_comp_level_target();
        $employees = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');
        // $employees = $this->m_emp->get_employee(3, 'oal.id');
        $data['employees'] = $this->create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_assess);
        $data['content'] = "competency/level_score_year_edit";
        $this->load->view('templates/header_footer', $data);
    }

    private function indexBy(array $rows, callable $keyfn)
    {
        $out = [];
        foreach ($rows as $r) {
            $key = $keyfn($r);
            $out[$key] = $r;
        }
        return $out;
    }

    private function groupBy(array $rows, callable $keyfn)
    {
        $out = [];
        foreach ($rows as $r) {
            $key = $keyfn($r);
            $out[$key][] = $r;
        }
        return $out;
    }

    function create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_assess)
    {
        // --- 1) Build fast lookup maps ---
        // scoreMap[NRP][comp_lvl_id] = clas_score
        $scoreMap = [];
        foreach ($cl_scores as $s) {
            $nrp = $s['NRP'];
            $clid = (int)$s['comp_lvl_id'];
            if (!isset($scoreMap[$nrp])) $scoreMap[$nrp] = [];
            $scoreMap[$nrp][$clid] = is_null($s['clas_score']) ? null : (float)$s['clas_score'];
        }

        // targetMap[oalp_id][comp_lvl_id] = target
        $targetMap = [];
        foreach ($cl_targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            if (!isset($targetMap[$oalp])) $targetMap[$oalp] = [];
            $targetMap[$oalp][$clid] = is_null($t['target']) ? null : (float)$t['target'];
        }

        // assessMap[NRP] = ['id'=>..., 'vendor'=>..., 'recommendation'=>..., 'score'=>...]
        // Jika 1 NRP punya >1 baris assess, ambil yang terakhir (atau silakan ubah logika sesuai kebutuhan)
        $assessMap = [];
        foreach ($cl_assess as $a) {
            $nrp = $a['NRP'];
            $assessMap[$nrp] = [
                'id'             => $a['id'] ?? null,
                'vendor'         => $a['vendor'] ?? null,
                'recommendation' => $a['recommendation'] ?? null,
                'score'          => isset($a['score']) ? (float)$a['score'] : null,
            ];
        }

        // Simpan daftar comp level id agar tidak akses array assoc di dalam loop
        $compLvlIds = array_map(fn($c) => (int)$c['id'], $comp_lvl);

        // --- 2) Compose hasil tanpa array_filter di dalam loop ---
        foreach ($employees as &$e) {
            $nrp   = $e['NRP'];
            $oalp  = isset($e['oalp_id']) ? (int)$e['oalp_id'] : null;

            $e['cl_score'] = [];
            $e['cl_target'] = [];

            foreach ($compLvlIds as $clid) {
                $e['cl_score'][$clid]  = $scoreMap[$nrp][$clid]   ?? null;
                $e['cl_target'][$clid] = ($oalp !== null && isset($targetMap[$oalp]))
                    ? ($targetMap[$oalp][$clid] ?? null)
                    : null;
            }

            if (isset($assessMap[$nrp])) {
                $e['cla_id']         = $assessMap[$nrp]['id'];
                $e['vendor']         = $assessMap[$nrp]['vendor'];
                $e['recommendation'] = $assessMap[$nrp]['recommendation'];
                $e['score']          = $assessMap[$nrp]['score'];
            } else {
                $e['cla_id'] = $e['vendor'] = $e['recommendation'] = null;
                $e['score'] = null;
            }
        }
        unset($e);

        return $employees;
    }

    public function submit($am_hash)
    {
        $year = $this->input->post('year');
        flash_swal('error', 'Score Submit Failed');
        $success = $this->m_c_l_s->submit();
        if ($success) {
            flash_swal('success', 'Score Submitted Successfully');
        }
        redirect("comp_settings/level_score/year/$am_hash?year=$year");
    }
}
