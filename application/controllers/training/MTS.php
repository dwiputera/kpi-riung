<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MTS extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('training/m_mts');
        $this->load->model('training/m_mts_user', 'm_m_u');
        $this->load->model('training/m_atmp');
    }

    function index()
    {
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $data['trainings'] = $this->m_mts->get_mts($year, 'trn_mts.year');
        $data['chart_mts_atmp'] = $this->m_mts->get_mts_atmp_chart($year);
        $data['chart_mts_status'] = $this->m_mts->get_mts_status_chart($year);
        $data['year'] = $year;
        $data['content'] = "training/MTS";
        $this->load->view('templates/header_footer', $data);
    }

    private function set_swal($type, $msg)
    {
        $this->session->set_flashdata('swal', ['type' => $type, 'message' => $msg]);
    }

    function edit($year)
    {
        $data['year'] = $year;
        $data['trainings'] = $this->m_mts->get_mts($year, 'trn_mts.year');
        $data['content'] = "training/MTS_edit";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        if ($this->input->post('proceed') == 'N') {
            redirect('training/MTS?year=' . $this->input->post('year'));
        }

        $payload = json_decode($this->input->post('json_data'), true);
        $year = $this->input->post('year');

        $success = $this->m_mts->submit($payload, $year);

        $this->session->set_flashdata('swal', [
            'type' => $success ? 'success' : 'error',
            'message' => $success ? "MTS edited successfully" : "Failed to update MTS"
        ]);

        redirect('training/MTS/edit/' . $year);
    }

    public function ATMP($mts_hash)
    {
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $mts = $this->m_mts->get_mts($mts_hash, 'md5(trn_mts.id)', false);
        $atmp = null;
        if ($mts['atmp_id']) $atmp = $this->m_atmp->get_atmp($mts['atmp_id'], "id", false);
        if (!$this->input->get('action')) {
            $data['mts'] = $mts;
            $data['atmp'] = $atmp;
            $data['atmps'] = $this->m_atmp->get_atmp($mts['atmp_id'], "id !=");
            $data['year'] = $year;
            $data['content'] = "training/MTS_ATMP";
        } else {
            $atmp_hash = $this->input->get('atmp_hash');
            $atmp = $this->db->get_where('trn_atmp', array('md5(id)' => $atmp_hash))->row_array();
            if ($this->input->get('action') == 'unassign') {
                $this->set_swal('error', 'ATMP Unassign Failed');
                $success = $this->m_mts->submit(['updates' => [['id' => $mts['id'], 'atmp_id' => null]]], $year);
                if ($success) $this->set_swal('success', 'ATMP Unassigned Successfully');
            }
            if ($this->input->get('action') == 'assign') {
                $this->set_swal('error', 'ATMP Assign Failed');
                $success = $this->m_mts->submit(['updates' => [['id' => $mts['id'], 'atmp_id' => $atmp['id']]]], $year);
                if ($success) $this->set_swal('success', 'ATMP Assigned Successfully');
            }
            redirect('training/MTS/ATMP/' . $mts_hash . '?year=' . $year);
        }
        $this->load->view('templates/header_footer', $data);
    }

    public function participants($mts_hash)
    {
        $data['type'] = "mts";
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $mts = $this->m_mts->get_mts($mts_hash, 'md5(trn_mts.id)', false);
        if (!$this->input->get('action')) {
            $data['mts'] = $mts;
            $data['participants'] = $this->m_m_u->get_mts_user($mts_hash, "md5(mts_id)");
            $data['year'] = $year;
            $this->load->model('organization/m_user');
            $data['users'] = $this->m_user->get_user();
            $data['content'] = "training/users";
        } else {
            if ($this->input->get('action') == 'assign') {
                $this->set_swal('error', 'Participants Assign Failed');
                $success = $this->m_m_u->add($mts['id']);
                if ($success) $this->set_swal('success', 'Participants Assigned Successfully');
            }
            redirect('training/MTS/participants/' . $mts_hash . '?year=' . $year);
        }
        $this->load->view('templates/header_footer', $data);
    }
}
