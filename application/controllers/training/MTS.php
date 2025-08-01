<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MTS extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('training/m_mts');
    }

    function index()
    {
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $data['trainings'] = $this->m_mts->get_mts($year, 'trn_mts.year');
        $data['chart'] = $this->m_mts->get_training_chart($year);
        $data['year'] = $year;
        $data['content'] = "training/MTS";
        $this->load->view('templates/header_footer', $data);
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
}
