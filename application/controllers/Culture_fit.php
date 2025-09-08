<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Culture_fit extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_culture_fit', 'm_cf');
    }

    public function index()
    {
        $year = $this->input->get('year') ?? date("Y");
        $data['year'] = $year;
        $data['culture_fit'] = $this->m_cf->get($year, 'year');
        $data['content'] = "culture_fit";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $payload = json_decode($this->input->post('json_data'), true);
        $year = $this->input->get('year');

        $success = $this->m_cf->submit($payload, $year);

        $this->session->set_flashdata('swal', [
            'type' => $success ? 'success' : 'error',
            'message' => $success ? "Culture Fit Edited Successfully" : "Failed to Update Culture Fit"
        ]);

        redirect('culture_fit?year=' . $year);
    }
}
