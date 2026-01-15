<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ipa_score extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_ipa_score', 'm_i');
    }

    public function index()
    {
        $year = date('Y');
        $data['year'] = $year;
        $data['ipa_scores'] = $this->m_i->get_current($year);
        $data['content'] = "ipa_score/current";
        $this->load->view('templates/header_footer', $data);
    }

    public function edit()
    {
        $year = $this->input->get('year') ?? date('Y');
        $data['year'] = $year;
        $data['ipa_scores'] = $this->m_i->get_current($year, "WHERE eis.tahun = $year");
        $data['content'] = "ipa_score/year";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $raw = $this->input->post('json_data');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            flash_swal('error', 'Payload invalid / kosong');
            redirect('ipa_score/edit');
            return;
        }

        $success = $this->m_i->submit($payload);

        flash_swal($success ? 'success' : 'error', $success ? "IPA Score Updated Successfully" : "Failed to Update IPA Score");

        // year untuk balik ke edit
        $year = $this->input->get('year');
        if (!$year) $year = $this->input->post('year');
        if (!$year && isset($payload['year'])) $year = $payload['year'];
        if (!$year) $year = date('Y');

        redirect('ipa_score/edit?year=' . (int)$year);
    }
}
