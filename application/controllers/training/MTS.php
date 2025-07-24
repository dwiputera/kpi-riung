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
        $month = $this->input->get('month');
        $month = $month ? $month : date('Y-m');
        $data['trainings'] = $this->m_mts->get_training($month);
        $data['chart'] = $this->m_mts->get_training_chart($month);
        $data['month'] = $month;
        $data['content'] = "training/MTS";
        $this->load->view('templates/header_footer', $data);
    }

    // function valid_month_format($str)
    // {
    //     if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $str)) {
    //         return TRUE;
    //     } else {
    //         $this->form_validation->set_message('valid_month_format', 'The {field} field must be in YYYY-MM format.');
    //         return FALSE;
    //     }
    // }

    function edit($month)
    {
        $data['month'] = $month;
        $data['trainings'] = $this->m_mts->get_training($month);
        $data['content'] = "training/MTS_edit";
        $this->load->view('templates/header_footer', $data);
    }

    function submit()
    {
        if ($this->input->post('proceed') == 'N') {
            redirect('training/MTS?month=' . $this->input->post('month'));
        }
        $success = $this->m_mts->submit();

        $this->session->set_flashdata('swal', [
            'type' => 'success',
            'message' => "MTS edited succesfully"
        ]);
        redirect('training/MTS/edit/' . $this->input->post('month'));
    }
}
