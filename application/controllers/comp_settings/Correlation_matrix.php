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
        $data['order'] = [50, 48, 49, 21, 23, 45, 54, 56, 57, 9, 51, 46, 47, 8];
        $data['correlation_matrix'] = array_column($this->m_c_pstn->get_correlation_matrix(), null, 'id');
        $data['content'] = "competency/correlation_matrix";
        $this->load->view('templates/header_footer', $data);
    }
}
