<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Position_score extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_position_target', 'm_c_p_t');
        $this->load->model('competency/m_comp_position_score', 'm_c_p_s');
        $this->load->model('organization/m_position', 'm_pstn');
    }

    public function index()
    {
        $data = [];
        $data['matrix_points'] = $this->db
            ->get_where('org_area_lvl_pstn', ['type' => 'matrix_point'])
            ->result_array();

        $data['content'] = "competency/position_score_matrix_point";
        $this->load->view('templates/header_footer', $data);
    }

    public function current($mp_hash)
    {
        // Default tetap +1 seperti versi lama
        $year        = $this->input->get('year', true);
        $year        = $year !== null && $year !== '' ? (int)$year : (int)date('Y') + 1;

        $data = [];
        $data['year']         = $year;
        $data['matrix_point'] = $this->db->get_where('org_area_lvl_pstn', ['md5(id)' => $mp_hash])->row_array();

        $comp_pstn = $this->db->get_where('comp_position', ['md5(area_lvl_pstn_id)' => $mp_hash])->result_array();
        $data['comp_pstn'] = $comp_pstn;

        $cp_scores  = $this->m_c_p_s->get_cp_score($mp_hash, "md5(area_lvl_pstn_id)");
        $cp_targets = $this->m_c_p_t->get_comp_position_target($mp_hash, 'md5(cp.area_lvl_pstn_id)');
        $employees  = $this->m_pstn->get_area_lvl_pstn_user($mp_hash, 'md5(mp_id)');

        $data['employees'] = $this->create_matrix($employees, $comp_pstn, $cp_scores, $cp_targets);

        $data['content'] = "competency/position_score";
        $this->load->view('templates/header_footer', $data);
    }

    public function year($mp_hash)
    {
        // Default tetap +1 seperti versi lama
        $year        = $this->input->get('year', true);
        $year        = $year !== null && $year !== '' ? (int)$year : (int)date('Y') + 1;

        $data = [];
        $data['year']         = $year;
        $data['matrix_point'] = $this->db->get_where('org_area_lvl_pstn', ['md5(id)' => $mp_hash])->row_array();

        $comp_pstn = $this->db->get_where('comp_position', ['md5(area_lvl_pstn_id)' => $mp_hash])->result_array();
        $data['comp_pstn'] = $comp_pstn;

        $cp_scores  = $this->m_c_p_s->get_cp_score_year($mp_hash, "year = $year AND md5(area_lvl_pstn_id)");
        $cp_targets = $this->m_c_p_t->get_comp_position_target($mp_hash, 'md5(cp.area_lvl_pstn_id)');
        $employees  = $this->m_pstn->get_area_lvl_pstn_user($mp_hash, 'md5(mp_id)');

        $data['employees'] = $this->create_matrix($employees, $comp_pstn, $cp_scores, $cp_targets);

        $data['content'] = "competency/position_score_year";
        $this->load->view('templates/header_footer', $data);
    }

    public function year_edit($mp_hash)
    {
        // Default tetap tahun berjalan seperti versi lama
        $year        = $this->input->get('year', true);
        $year        = $year !== null && $year !== '' ? (int)$year : (int)date('Y');

        $data = [];
        $data['year']         = $year;
        $data['matrix_point'] = $this->db->get_where('org_area_lvl_pstn', ['md5(id)' => $mp_hash])->row_array();

        $comp_pstn = $this->db->get_where('comp_position', ['md5(area_lvl_pstn_id)' => $mp_hash])->result_array();
        $data['comp_pstn'] = $comp_pstn;

        $cp_scores  = $this->m_c_p_s->get_cp_score_year($mp_hash, "year = $year AND md5(area_lvl_pstn_id)");
        $cp_targets = $this->m_c_p_t->get_comp_position_target($mp_hash, 'md5(cp.area_lvl_pstn_id)');
        $employees  = $this->m_pstn->get_area_lvl_pstn_user($mp_hash, 'md5(mp_id)');

        $data['employees'] = $this->create_matrix($employees, $comp_pstn, $cp_scores, $cp_targets);

        $data['content'] = "competency/position_score_year_edit";
        $this->load->view('templates/header_footer', $data);
    }

    /**
     * OPTIMIZED:
     * - Build index scoreMap[NRP][comp_pstn_id] = score
     * - Build index targetMap[oalp_id][comp_pstn_id] = target
     * - Satu pass isi tiap employee (tanpa array_filter nested)
     */
    private function create_matrix(array $employees, array $comp_pstn, array $cp_scores, array $cp_targets): array
    {
        // 1) Index skor aktual per NRP & competency position
        $scoreMap = [];
        foreach ($cp_scores as $row) {
            // Pastikan field sesuai model kamu: 'NRP', 'comp_pstn_id', 'score'
            $nrp  = $row['NRP'];
            $cpid = (int)$row['comp_pstn_id'];
            // Ambil nilai apa adanya; jika null biarkan null
            $scoreMap[$nrp][$cpid] = isset($row['score']) ? (float)$row['score'] : null;
        }

        // 2) Index target per OALP & competency position
        $targetMap = [];
        foreach ($cp_targets as $row) {
            // Pastikan field sesuai model kamu: 'cpt_oalp_id', 'comp_pstn_id', 'target'
            $oalp = (int)$row['cpt_oalp_id'];
            $cpid = (int)$row['comp_pstn_id'];
            $targetMap[$oalp][$cpid] = isset($row['target']) ? (float)$row['target'] : null;
        }

        // 3) Daftar id competency position untuk sekali loop
        $cpIds = [];
        foreach ($comp_pstn as $cp) {
            $cpIds[] = (int)$cp['id'];
        }

        // 4) Isi setiap employee
        foreach ($employees as &$e) {
            $nrp  = $e['NRP'];
            $oalp = isset($e['oalp_id']) ? (int)$e['oalp_id'] : null;

            $e['cp_score']  = [];
            $e['cp_target'] = [];

            $scoreRow  = $scoreMap[$nrp]  ?? null;
            $targetRow = ($oalp !== null) ? ($targetMap[$oalp] ?? null) : null;

            foreach ($cpIds as $cpid) {
                $e['cp_score'][$cpid]  = $scoreRow  !== null ? ($scoreRow[$cpid]  ?? null) : null;
                $e['cp_target'][$cpid] = $targetRow !== null ? ($targetRow[$cpid] ?? null) : null;
            }
        }
        unset($e);

        return $employees;
    }

    public function submit($mp_hash)
    {
        $year = (int)$this->input->post('year');
        flash_swal('error', 'Score Submit Failed');

        $success = $this->m_c_p_s->submit();
        if ($success) {
            flash_swal('success', 'Score Submitted Successfully');
        }

        redirect("comp_settings/position_score/year/$mp_hash?year=$year");
    }
}
