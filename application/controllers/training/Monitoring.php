<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoring extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('training/m_monitoring');
        $this->load->model('training/m_atmp');
        $this->load->model('training/m_mts');
    }

    function index()
    {
        $year_month = $this->input->get('year_month');
        $year_month = $year_month ? $year_month : date('Y-m');
        $year = substr($year_month, 0, 4);
        $month = substr($year_month, 5, 2);
        $atmp = $this->m_atmp->get_atmp($year, 'year');
        $mts = $this->m_mts->get_mts($year, 'trn_mts.year');
        $data['trainings']['mtd'] = $this->m_monitoring->get_training($year, $month, $atmp, $mts, 'mtd');
        $data['chart_status']['mtd'] = $this->m_monitoring->get_chart_status($year, $month, $atmp, $mts, 'mtd');
        $data['chart_budget']['mtd'] = $this->m_monitoring->get_chart_budget($year, $month, $atmp, $mts, 'mtd');
        $data['chart_participants']['mtd'] = $this->m_monitoring->get_chart_participants($year, $month, $atmp, $mts, 'mtd');
        $data['trainings']['ytd'] = $this->m_monitoring->get_training($year, $month, $atmp, $mts, 'ytd');
        $data['chart_status']['ytd'] = $this->m_monitoring->get_chart_status($year, $month, $atmp, $mts, 'ytd');
        $data['chart_budget']['ytd'] = $this->m_monitoring->get_chart_budget($year, $month, $atmp, $mts, 'ytd');
        $data['chart_participants']['ytd'] = $this->m_monitoring->get_chart_participants($year, $month, $atmp, $mts, 'ytd');
        $data['year_month'] = $year_month;
        $data['year_month_str'] = date("F", strtotime($year_month));;
        $data['year'] = $year;
        $data['month'] = $month;
        $data['content'] = "training/monitoring";
        $this->load->view('templates/header_footer', $data);
    }

    // function edit($month)
    // {
    //     $data['month'] = $month;
    //     $data['trainings'] = $this->m_monitoring->get_training($month);
    //     $data['content'] = "training/monitoring_edit";
    //     $this->load->view('templates/header_footer', $data);
    // }

    // function submit()
    // {
    //     if ($this->input->post('proceed') == 'N') {
    //         redirect('training/monitoring?month=' . $this->input->post('month'));
    //     }
    //     $success = $this->m_monitoring->submit();

    //     $this->session->set_flashdata('swal', [
    //         'type' => 'success',
    //         'message' => "monitoring edited succesfully"
    //     ]);
    //     redirect('training/monitoring/edit/' . $this->input->post('month'));
    // }

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
