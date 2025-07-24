<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoring extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('training/m_monitoring');
    }

    function index()
    {
        $month = $this->input->get('month');
        $month = $month ? $month : date('Y-m');
        $year = substr($month, 0, 4);
        $data['trainings']['mtd'] = $this->m_monitoring->get_training($month);
        $data['trainings']['ytd'] = $this->m_monitoring->get_training($month, $year);
        $data['chart_status']['mtd'] = $this->m_monitoring->get_chart_status($month);
        $data['chart_budget']['mtd'] = $this->m_monitoring->get_chart_budget($month);
        $data['chart_participants']['mtd'] = $this->m_monitoring->get_chart_participants($month);
        $data['chart_status']['ytd'] = $this->m_monitoring->get_chart_status($month, $year);
        $data['chart_budget']['ytd'] = $this->m_monitoring->get_chart_budget($month, $year);
        $data['chart_participants']['ytd'] = $this->m_monitoring->get_chart_participants($month, $year);
        $data['month'] = $month;
        $data['month_str'] = date("F", strtotime($month));;
        $data['year'] = $year;
        $data['content'] = "training/monitoring";
        $this->load->view('templates/header_footer', $data);
    }

    function edit($month)
    {
        $data['month'] = $month;
        $data['trainings'] = $this->m_monitoring->get_training($month);
        $data['content'] = "training/monitoring_edit";
        $this->load->view('templates/header_footer', $data);
    }

    function submit()
    {
        if ($this->input->post('proceed') == 'N') {
            redirect('training/monitoring?month=' . $this->input->post('month'));
        }
        $success = $this->m_monitoring->submit();

        $this->session->set_flashdata('swal', [
            'type' => 'success',
            'message' => "monitoring edited succesfully"
        ]);
        redirect('training/monitoring/edit/' . $this->input->post('month'));
    }

    // function test($function, $param)
    // {
    //     $this->load->model('m_access');
    //     switch ($function) {
    //         case 'get_menus_by_role':
    //             $data = $this->m_access->get_menus_by_role($param);
    //             echo '<pre>', var_dump($data);
    //             die;
    //             break;
    //         default:
    //             break;
    //     }
    // }
}
