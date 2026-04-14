<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RTC extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_rtc', 'm_rtc');
        $this->load->model('organization/m_position');
    }

    public function index()
    {
        $year = (int) $this->input->get('year');
        if (!$year) {
            $year = (int) date('Y');
        }

        $years = [];
        for ($i = 0; $i < 5; $i++) {
            $years[] = $year + $i;
        }

        $positions = $this->m_position->get_area_lvl_pstn(null, 'md5(oalp.id)', true);
        $rows = $this->m_rtc->build_rtc_rows($positions, $years);

        $data['title'] = 'Replacement Table Chart';
        $data['year'] = $year;
        $data['years'] = $years;
        $data['rows'] = $rows;
        $data['content'] = 'RTC/list';

        $this->load->view('templates/header_footer', $data);
    }

    public function candidate_options()
    {
        $keyword = trim((string) $this->input->get('q'));
        $result = $this->m_rtc->get_candidate_options($keyword);

        $items = [];
        foreach ($result as $r) {
            $items[] = [
                'id'   => $r['NRP'],
                'text' => $r['NRP'] . ' - ' . $r['FullName'],
                'nrp'  => $r['NRP'],
                'name' => $r['FullName'],
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'results' => $items
            ]));
    }

    public function get_year_assignment()
    {
        $oalp_id = (int) $this->input->get('oalp_id');
        $year    = (int) $this->input->get('year');

        if (!$oalp_id || !$year) {
            return $this->json_response([
                'status'  => false,
                'message' => 'Parameter tidak lengkap.'
            ], 400);
        }

        $assigned = $this->m_rtc->get_assignment_with_name($oalp_id, $year);

        $selected = [];
        foreach ($assigned as $a) {
            $selected[] = [
                'id'   => $a['NRP'],
                'text' => $a['NRP'] . ' - ' . $a['FullName'],
                'nrp'  => $a['NRP'],
                'name' => $a['FullName'],
            ];
        }

        return $this->json_response([
            'status'   => true,
            'selected' => $selected
        ]);
    }

    public function save()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $year = (int) $this->input->post('base_year');
        $rows = $this->input->post('rows');

        if (!$year) {
            $year = (int) date('Y');
        }

        if (is_string($rows)) {
            $rows = json_decode($rows, true);
        }

        if (!is_array($rows)) {
            return $this->json_response([
                'status'  => false,
                'message' => 'Payload rows tidak valid.'
            ], 400);
        }

        $this->db->trans_begin();

        try {
            foreach ($rows as $row) {
                $oalp_id = isset($row['oalp_id']) ? (int) $row['oalp_id'] : 0;
                if (!$oalp_id || empty($row['years']) || !is_array($row['years'])) {
                    continue;
                }

                foreach ($row['years'] as $yearKey => $nrps) {
                    $yearValue = (int) $yearKey;

                    if (!is_array($nrps)) {
                        $nrps = [];
                    }

                    $cleanNrps = [];
                    foreach ($nrps as $nrp) {
                        $nrp = trim((string) $nrp);
                        if ($nrp !== '') {
                            $cleanNrps[] = $nrp;
                        }
                    }

                    $this->m_rtc->replace_assignment($oalp_id, $yearValue, $cleanNrps);
                }
            }

            if ($this->db->trans_status() === false) {
                throw new Exception('Transaksi gagal.');
            }

            $this->db->trans_commit();

            return $this->json_response([
                'status'  => true,
                'message' => 'RTC berhasil disimpan.'
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();

            return $this->json_response([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function json_response($data = [], $statusCode = 200)
    {
        return $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
