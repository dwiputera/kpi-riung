<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Health_status extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_health_status', 'm_hs');
    }

    public function index()
    {
        $year = $this->input->get('year') ?? date("Y");
        $data['year'] = $year;
        $data['health_status'] = $this->m_hs->get_current();
        $data['content'] = "health_status/health_status";
        $this->load->view('templates/header_footer', $data);
    }

    public function edit()
    {
        $year = $this->input->get('year') ?? date("Y");
        $data['year'] = $year;
        $data['health_status'] = $this->m_hs->get("WHERE hsu.year = $year");
        $data['content'] = "health_status/health_status_edit";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $payload = json_decode($this->input->post('json_data'), true);
        $year = $this->input->get('year');

        $success = $this->m_hs->submit($payload, $year);

        flash_swal($success ? 'success' : 'error', $success ? "Health Status Edited Successfully" : "Failed to Update Health Status");

        redirect('health_status/edit?year=' . $year);
    }
}
