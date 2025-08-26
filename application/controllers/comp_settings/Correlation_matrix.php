<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Correlation_matrix extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_position', 'm_c_pstn');
        $this->load->model('organization/m_position', 'm_pstn');
    }

    public function index()
    {
        $data['correlation_matrix'] = $this->m_c_pstn->get_correlation_matrix();
        $data['content'] = "competency/correlation_matrix";
        $this->load->view('templates/header_footer', $data);
    }
}
