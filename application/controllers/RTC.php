<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RTC extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_rtc', 'm_rtc');
        $this->load->model('organization/m_position', 'm_p');
    }

    public function index()
    {
        $data['positions'] = $this->m_p->get_area_lvl_pstn_user();
        $year1 = date("Y") + 1;
        $year2 = date("Y") + 2;
        $data['rtcs'] = $this->m_rtc->get("WHERE year IN($year1, $year2)");
        $data['content'] = "RTC/list";
        $this->load->view('templates/header_footer', $data);
    }

    public function edit()
    {
        // ambil year dari GET
        $year = (int) $this->input->get('year');
        if (!$year) {
            $year = date('Y'); // default
        }

        $data['year']  = $year;
        $data['year1'] = $year + 1;
        $data['year2'] = $year + 2;

        $data['positions'] = $this->m_p->get_area_lvl_pstn_user();
        $data['users'] = $this->db
            ->get_where("rml_sso_la.users", ['EmployeeGroup' => 'Active'])
            ->result_array();

        $data['rtcs'] = $this->m_rtc->get("WHERE year IN ({$data['year1']}, {$data['year2']})");

        $data['content'] = "RTC/edit";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $raw = $this->input->post('json_data');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            flash_swal('error', 'Payload invalid / kosong');
            redirect('RTC/edit');
            return;
        }

        $success = $this->m_rtc->submit($payload);

        flash_swal($success ? 'success' : 'error', $success ? "RTC Updated Successfully" : "Failed to Update RTC");

        $baseYear = $this->input->post('year') ?: ($payload['year1'] - 1); // base = year1-1
        redirect('RTC/edit?year=' . (int)$baseYear);
    }
}
