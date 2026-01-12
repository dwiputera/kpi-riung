<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tour_of_duty extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_tour_of_duty', 'm_tod');
        $this->load->model('organization/m_position', 'm_p');
    }

    public function index()
    {
        $data['tour_of_duties'] = $this->m_tod->get_tod_with_mp();
        $data['matrix_points'] = $this->m_p->get_matrix_point();
        $data['content'] = "tour_of_duty/list";
        $this->load->view('templates/header_footer', $data);
    }

    public function edit()
    {
        $data['tour_of_duties'] = $this->m_tod->get_tod_with_mp();
        $data['matrix_points'] = $this->m_p->get_matrix_point();
        $data['content'] = "tour_of_duty/edit";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $payload = json_decode($this->input->post('json_data'), true);

        $success = $this->m_tod->submit($payload);

        flash_swal($success ? 'success' : 'error', $success ? "Tour Of Duty Updated Successfully" : "Failed to Update Tour Of Duty");

        redirect('tour_of_duty/edit');
    }
}
