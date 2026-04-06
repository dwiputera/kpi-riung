<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Observation extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('m_observation');
        $this->load->helper(['form', 'url']);
        $this->load->library('session');
    }

    public function index()
    {
        $date_from = $this->input->get('date_from', true);
        $date_to   = $this->input->get('date_to', true);

        // Default 7 hari terakhir
        if (empty($date_from) && empty($date_to)) {
            $date_to   = date('Y-m-d');
            $date_from = date('Y-m-d', strtotime('-6 days'));
        }

        if (!empty($date_from) && empty($date_to)) {
            $date_to = date('Y-m-d');
        }

        if (empty($date_from) && !empty($date_to)) {
            $date_from = date('Y-m-d', strtotime($date_to . ' -6 days'));
        }

        $filters = [
            'date_from' => $date_from,
            'date_to'   => $date_to,
        ];

        $data['filters']      = $filters;
        $data['observations'] = $this->m_observation->get_all_observations($filters);
        $data['content']      = 'observation_list';

        $this->load->view('templates/header_footer', $data);
    }

    public function add()
    {
        $data = $this->_get_form_master_data();
        $data['content'] = 'observation_submit';

        $this->load->view('templates/header_footer', $data);
    }

    public function save()
    {
        $post = $this->input->post(null, true);

        if (empty($post)) {
            show_404();
        }

        $validation = $this->_validate_relation($post);
        if ($validation !== true) {
            $this->session->set_flashdata('error', $validation);
            redirect('observation/add');
        }

        $data = $this->_prepare_observation_data($post);

        $insert_id = $this->m_observation->insert($data);

        if ($insert_id) {
            $this->session->set_flashdata('success', 'Data observation berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Data observation gagal disimpan.');
        }

        redirect('observation');
    }

    public function edit($id = null)
    {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $observation = $this->m_observation->get_observation_by_id($id);

        if (!$observation) {
            show_404();
        }

        if (!empty($observation['fci'])) {
            $decoded = json_decode($observation['fci'], true);
            $observation['fci'] = is_array($decoded) ? $decoded : [];
        } else {
            $observation['fci'] = [];
        }

        $data = $this->_get_form_master_data();
        $data['observation'] = $observation;
        $data['content']     = 'observation_submit';

        $this->load->view('templates/header_footer', $data);
    }

    public function update($id = null)
    {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $existing = $this->m_observation->get_observation_by_id($id);
        if (!$existing) {
            show_404();
        }

        $post = $this->input->post(null, true);

        if (empty($post)) {
            show_404();
        }

        $validation = $this->_validate_relation($post);
        if ($validation !== true) {
            $this->session->set_flashdata('error', $validation);
            redirect('observation/edit/' . $id);
        }

        $data = $this->_prepare_observation_data($post);

        $updated = $this->m_observation->update($id, $data);

        if ($updated) {
            $this->session->set_flashdata('success', 'Data observation berhasil diupdate.');
        } else {
            $this->session->set_flashdata('error', 'Data observation gagal diupdate.');
        }

        redirect('observation');
    }

    public function delete($id = null)
    {
        if (!$id || !is_numeric($id)) {
            show_404();
        }

        $existing = $this->m_observation->get_observation_by_id($id);
        if (!$existing) {
            show_404();
        }

        $deleted = $this->m_observation->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Data observation berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Data observation gagal dihapus.');
        }

        redirect('observation');
    }

    private function _get_form_master_data()
    {
        $data['operators'] = $this->db
            ->where('EmployeeSubgroup', 'Operator')
            ->order_by('FullName', 'ASC')
            ->get('rml_sso_la.users')
            ->result_array();

        $data['areas'] = $this->db
            ->where('name !=', 'RMHO')
            ->order_by('name', 'ASC')
            ->get('org_area')
            ->result_array();

        $data['pits'] = $this->db
            ->order_by('name', 'ASC')
            ->get('org_area_pit')
            ->result_array();

        $data['equipments'] = $this->db
            ->like('model_number', 'EX', 'after')
            ->order_by('equipment', 'ASC')
            ->get('equipments')
            ->result_array();

        return $data;
    }

    private function _validate_relation($post)
    {
        $job_site_id   = $this->_number($post, 'job_site');
        $pit_location  = $this->_number($post, 'pit_location');
        $unit_type     = $this->_number($post, 'unit_type');

        if (empty($job_site_id)) {
            return 'Job Site wajib dipilih.';
        }

        if (!empty($pit_location)) {
            $pit = $this->db
                ->where('id', $pit_location)
                ->where('area_id', $job_site_id)
                ->get('org_area_pit')
                ->row_array();

            if (!$pit) {
                return 'Lokasi pit tidak sesuai dengan job site yang dipilih.';
            }
        }

        if (!empty($unit_type)) {
            $area = $this->db
                ->select('id, name')
                ->where('id', $job_site_id)
                ->limit(1)
                ->get('org_area')
                ->row_array();

            if (!$area) {
                return 'Job site tidak ditemukan.';
            }

            $equipment = $this->db
                ->where('id', $unit_type)
                ->where('maint_plant', $area['name'])
                ->get('equipments')
                ->row_array();

            if (!$equipment) {
                return 'Tipe unit tidak sesuai dengan job site yang dipilih.';
            }
        }

        return true;
    }

    private function _prepare_observation_data($post)
    {
        return [
            // INFORMASI UMUM
            'NRP'                      => $this->_text($post, 'NRP'),
            'date'                     => $this->_text($post, 'date'),
            'job_site'                 => $this->_number($post, 'job_site'),
            'pit_location'             => $this->_number($post, 'pit_location'),
            'unit_type'                => $this->_number($post, 'unit_type'),
            'observer'                 => $this->session->userdata('NRP'),
            'observer_name'            => $this->session->userdata('full_name'),

            // PARAMETER OPERASIONAL
            'haul_distance'            => $this->_number($post, 'haul_distance'),
            'hauler_count'             => $this->_number($post, 'hauler_count'),
            'material_type'            => $this->_text($post, 'material_type'),
            'front_width'              => $this->_text($post, 'front_width'),
            'unit_condition'           => $this->_text($post, 'unit_condition'),
            'cycle_time'               => $this->_number($post, 'cycle_time'),

            // FCI
            'fci'                      => isset($post['fci']) && is_array($post['fci'])
                ? json_encode($post['fci'])
                : json_encode([]),

            // INPUT OBSERVASI PRIMARY
            'digging_time'                 => $this->_number($post, 'digging_time'),
            'swing_load_time'              => $this->_number($post, 'swing_load_time'),
            'dump_time'                    => $this->_number($post, 'dump_time'),
            'swing_empty_primary'          => $this->_number($post, 'swing_empty_primary'),
            'avg_cycle_time_primary'       => $this->_number($post, 'avg_cycle_time_primary'),
            'duration_observation_primary' => $this->_number($post, 'duration_observation_primary'),
            'average_passing'              => $this->_number($post, 'average_passing'),

            // INPUT OBSERVASI SECONDARY
            'idle_time'                    => $this->_number($post, 'idle_time'),
            'positioning_time'             => $this->_number($post, 'positioning_time'),
            'wait_to_dump'                 => $this->_number($post, 'wait_to_dump'),
            'swing_empty_secondary'        => $this->_number($post, 'swing_empty_secondary'),
            'avg_cycle_time_secondary'     => $this->_number($post, 'avg_cycle_time_secondary'),
            'duration_observation_secondary' => $this->_number($post, 'duration_observation_secondary'),

            // DEVIASI FRONT
            'front_amblas'              => $this->_number($post, 'front_amblas'),
            'front_licin'               => $this->_number($post, 'front_licin'),
            'front_menanjak'            => $this->_number($post, 'front_menanjak'),
            'front_berair'              => $this->_number($post, 'front_berair'),
            'front_perbaikan'           => $this->_number($post, 'front_perbaikan'),
            'front_crowded'             => $this->_number($post, 'front_crowded'),
            'front_berdebu'             => $this->_number($post, 'front_berdebu'),
            'front_sempit'              => $this->_number($post, 'front_sempit'),
            'general_front'             => $this->_number($post, 'general_front'),
            'front_lembek'              => $this->_number($post, 'front_lembek'),
            'front_undulating'          => $this->_number($post, 'front_undulating'),

            // DEVIASI MATERIAL
            'mat_blasting_keras'        => $this->_number($post, 'mat_blasting_keras'),
            'mat_boulder_frag_besar'    => $this->_number($post, 'mat_boulder_frag_besar'),

            // DEVIASI EQUIPMENT
            'under_truck'               => $this->_number($post, 'under_truck'),
            'unit_dm'                   => $this->_number($post, 'unit_dm'),
            'matching_fleet'            => $this->_number($post, 'matching_fleet'),
            'exca_refueling'            => $this->_number($post, 'exca_refueling'),

            // DEVIASI OPERATOR - SKILL
            'kombinasi_attch'           => $this->_number($post, 'kombinasi_attch'),
            'loading_method_dev'        => $this->_number($post, 'loading_method_dev'),

            // DEVIASI OPERATOR - KNOWLEDGE
            'product_knowledge'         => $this->_number($post, 'product_knowledge'),
            'method_knowledge'          => $this->_number($post, 'method_knowledge'),
            'reporting'                 => $this->_number($post, 'reporting'),
            'safety_operation'          => $this->_number($post, 'safety_operation'),

            // DEVIASI OPERATOR - ATTITUDE
            'attitude_opt'              => $this->_text($post, 'attitude_opt'),

            // ANALISA & EVIDENCE
            'deviation_front'           => $this->_text($post, 'deviation_front'),
            'recommendation'            => $this->_text($post, 'recommendation'),
            'image_url'                 => $this->_text($post, 'image_url'),
            'evidence_loader_portrait'  => $this->_text($post, 'evidence_loader_portrait'),
            'evidence_loader_landscape' => $this->_text($post, 'evidence_loader_landscape'),
        ];
    }

    private function _text($post, $key)
    {
        return isset($post[$key]) && trim((string)$post[$key]) !== ''
            ? trim((string)$post[$key])
            : null;
    }

    private function _number($post, $key)
    {
        if (!isset($post[$key]) || $post[$key] === '') {
            return null;
        }

        $value = str_replace(',', '.', trim((string)$post[$key]));

        return is_numeric($value) ? $value : null;
    }
}
