<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Level_score extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_level_target', 'm_c_l_t');
        $this->load->model('competency/m_comp_level_score',  'm_c_l_s');
        $this->load->model('organization/m_level',           'm_lvl');
        $this->load->model('employee/m_employee',            'm_emp');
    }

    public function index()
    {
        $data = [];
        $data['assess_methods'] = $this->db->get('comp_lvl_assess_method')->result_array();
        $data['content'] = "competency/level_score_assess_method";
        $this->load->view('templates/header_footer', $data);
    }

    public function current($am_hash)
    {
        // Default: tahun berjalan + 1 (sesuai kode lama)
        $year = $this->input->get('year', true);
        $year = ($year !== null && $year !== '') ? (int)$year : ((int)date('Y') + 1);

        $data = [];
        $data['year'] = $year;

        $data['assess_method'] = $this->db
            ->get_where('comp_lvl_assess_method', ['md5(id)' => $am_hash])
            ->row_array();

        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $data['comp_lvl'] = $comp_lvl;

        // cl_assess dipakai untuk kolom vendor/recommendation/score (tanpa filter tahun di current)
        $cl_assess  = $this->db->get_where('comp_lvl_assess', ['md5(method_id)' => $am_hash])->result_array();
        $cl_scores  = $this->m_c_l_s->get_cl_score($am_hash, "md5(method_id)");
        $cl_targets = $this->m_c_l_t->get_comp_level_target();
        $employees  = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');

        $data['employees'] = $this->create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_assess);

        $data['content'] = "competency/level_score";
        $this->load->view('templates/header_footer', $data);
    }

    public function year($am_hash)
    {
        // Default: tahun berjalan + 1 (sesuai kode lama)
        $year = $this->input->get('year', true);
        $year = ($year !== null && $year !== '') ? (int)$year : ((int)date('Y') + 1);

        $data = [];
        $data['year'] = $year;

        $data['assess_method'] = $this->db
            ->get_where('comp_lvl_assess_method', ['md5(id)' => $am_hash])
            ->row_array();

        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $data['comp_lvl'] = $comp_lvl;

        // year-based data
        $cl_assess  = $this->db->get_where('comp_lvl_assess', ['tahun' => $year, 'md5(method_id)' => $am_hash])->result_array();
        $cl_scores  = $this->m_c_l_s->get_cl_score_year($am_hash, "tahun = '$year' AND md5(method_id)");
        $cl_targets = $this->m_c_l_t->get_comp_level_target();
        $employees  = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');

        $data['employees'] = $this->create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_assess);

        $data['content'] = "competency/level_score_year";
        $this->load->view('templates/header_footer', $data);
    }

    public function year_edit($am_hash)
    {
        // Default: tahun berjalan (sesuai kode lama)
        $year = $this->input->get('year', true);
        $year = ($year !== null && $year !== '') ? (int)$year : (int)date('Y');

        $data = [];
        $data['year'] = $year;

        $data['assess_method'] = $this->db
            ->get_where('comp_lvl_assess_method', ['md5(id)' => $am_hash])
            ->row_array();

        $comp_lvl = $this->db->get('comp_lvl')->result_array();
        $data['comp_lvl'] = $comp_lvl;

        // year-based data
        $cl_assess  = $this->db->get_where('comp_lvl_assess', ['tahun' => $year, 'md5(method_id)' => $am_hash])->result_array();
        $cl_scores  = $this->m_c_l_s->get_cl_score_year($am_hash, "tahun = '$year' AND md5(method_id)");
        $cl_targets = $this->m_c_l_t->get_comp_level_target();
        $employees  = $this->m_emp->get_employee('IS NOT NULL', 'oalp.id');

        $data['employees'] = $this->create_matrix($employees, $comp_lvl, $cl_scores, $cl_targets, $cl_assess);

        $data['content'] = "competency/level_score_year_edit";
        $this->load->view('templates/header_footer', $data);
    }

    /**
     * Build matriks untuk tiap pegawai:
     *  - cl_score[comp_lvl_id]
     *  - cl_target[comp_lvl_id]
     *  - cla_id, vendor, recommendation, score (dari cl_assess)
     *
     * Kompleksitas: O(E + S + T + A)
     */
    private function create_matrix(array $employees, array $comp_lvl, array $cl_scores, array $cl_targets, array $cl_assess): array
    {
        // 1) Index skor aktual per NRP & comp level
        //    scoreMap[NRP][comp_lvl_id] = clas_score
        $scoreMap = [];
        foreach ($cl_scores as $s) {
            $nrp  = $s['NRP'];
            $clid = (int)$s['comp_lvl_id'];
            $scoreMap[$nrp][$clid] = isset($s['clas_score']) ? (float)$s['clas_score'] : null;
        }

        // 2) Index target per OALP & comp level
        //    targetMap[oalp_id][comp_lvl_id] = target
        $targetMap = [];
        foreach ($cl_targets as $t) {
            $oalp = (int)$t['area_lvl_pstn_id'];
            $clid = (int)$t['comp_lvl_id'];
            $targetMap[$oalp][$clid] = isset($t['target']) ? (float)$t['target'] : null;
        }

        // 3) Index assessment (ambil terakhir per NRP bila dobel)
        //    assessMap[NRP] = ['id','vendor','recommendation','score']
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

        // 4) Ambil list id competency sekali saja
        $compLvlIds = [];
        foreach ($comp_lvl as $c) $compLvlIds[] = (int)$c['id'];

        // 5) Compose hasil untuk tiap karyawan
        foreach ($employees as &$e) {
            $nrp  = $e['NRP'];
            $oalp = isset($e['oalp_id']) ? (int)$e['oalp_id'] : null;

            $e['cl_score']  = [];
            $e['cl_target'] = [];

            $sRow = $scoreMap[$nrp]  ?? null;
            $tRow = ($oalp !== null) ? ($targetMap[$oalp] ?? null) : null;

            foreach ($compLvlIds as $clid) {
                $e['cl_score'][$clid]  = $sRow !== null ? ($sRow[$clid] ?? null) : null;
                $e['cl_target'][$clid] = $tRow !== null ? ($tRow[$clid] ?? null) : null;
            }

            if (isset($assessMap[$nrp])) {
                $e['cla_id']         = $assessMap[$nrp]['id'];
                $e['vendor']         = $assessMap[$nrp]['vendor'];
                $e['recommendation'] = $assessMap[$nrp]['recommendation'];
                $e['score']          = $assessMap[$nrp]['score'];
            } else {
                $e['cla_id'] = $e['vendor'] = $e['recommendation'] = null;
                $e['score']  = null;
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
