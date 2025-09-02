<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_tna_2024 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    function import($matrix_point = 'hcgs', $extract = false)
    {
        $excels = [
            "hcgs" => "TNA Hardcompetency HCGS.xlsx",
            "finance" => "Resume TNA 2024_ Finance.xlsx",
            "engineering" => "Final Form TNA Fungsional 2024_Egineering.xlsx",
            "operation" => "TNA Fungsional 2024 - Operation 091123 (2).xlsx",
            "hse" => "Summary TNA Fungsional 2024 - HSE All Sites (1).xlsx",
        ];

        if ($this->session->userdata('matrix_point') != $matrix_point || $extract == true) {
            $this->load->helper(['conversion', 'extract_spreadsheet']);
            $data['participants_excel'] = extract_spreadsheet("./uploads/imports_admin/TNA 2024/$excels[$matrix_point]") ?? [];
            $data['matrix_point'] = $matrix_point;
            $this->session->set_userdata($data);
        }

        $method = 'import_' . $matrix_point;
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            show_error("Method $method tidak ditemukan.");
        }
    }

    function import_hcgs()
    {
        $participants_excel = $this->session->userdata('participants_excel')[1];
        $participants_excel = array_filter($participants_excel, fn($pe_i, $i_pe) => $i_pe >= 7 && ($pe_i[3] || $pe_i[2]), ARRAY_FILTER_USE_BOTH);
        foreach ($participants_excel as $i_pe => $pe_i) {
            $data = [
                'year' => 2024,
                'matrix_point' => 21,
                'name' => $pe_i[2],
                'NRP' => $pe_i[3],
                'comp_pstn' => $pe_i[6],
                'target' => $pe_i[9],
                'score' => $pe_i[10],
            ];
            echo '<pre>', print_r($data, true);
            $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
            if (!$exist) {
                $success = $this->db->insert('comp_pstn_score', $data);
                echo '<pre>', print_r($success, true);
            }
        }
    }

    function import_hse()
    {
        $participants_excel = $this->session->userdata('participants_excel')[1];
        $participants_excel = array_filter($participants_excel, fn($pe_i, $i_pe) => $pe_i && $i_pe >= 7 && ($pe_i[2] || $pe_i[3]), ARRAY_FILTER_USE_BOTH);
        foreach ($participants_excel as $i_pe => $pe_i) {
            $data = [
                'year' => 2024,
                'matrix_point' => 54,
                'name' => $pe_i[2],
                'NRP' => $pe_i[3],
                'comp_pstn' => $pe_i[9],
                'target' => $pe_i[10],
                'score' => $pe_i[11],
            ];
            echo '<pre>', print_r($data, true);
            $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
            if (!$exist) {
                $success = $this->db->insert('comp_pstn_score', $data);
                echo '<pre>', print_r($success, true);
            }
        }
    }

    function import_engineering()
    {
        $participants_excel = $this->session->userdata('participants_excel')[7];
        // $participants_excel = $this->session->userdata('participants_excel')[11];
        $participants_excel = array_filter($participants_excel, fn($pe_i, $i_pe) => $i_pe >= 7 && ($pe_i[3] || $pe_i[2]), ARRAY_FILTER_USE_BOTH);
        foreach ($participants_excel as $i_pe => $pe_i) {
            $data = [
                'year' => 2024,
                'matrix_point' => 45,
                'name' => $pe_i[2],
                'NRP' => $pe_i[3],
                'comp_pstn' => $pe_i[9],
                'target' => $pe_i[10],
                'score' => $pe_i[11],
            ];
            echo '<pre>', print_r($data, true);
            $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
            if (!$exist) {
                $success = $this->db->insert('comp_pstn_score', $data);
                echo '<pre>', print_r($success, true);
            }
        }
    }

    function import_operation()
    {
        $participants_excel = $this->session->userdata('participants_excel')[0];
        $participants_excel = array_filter($participants_excel, fn($pe_i, $i_pe) => $i_pe >= 4 && ($pe_i[3] || $pe_i[2]), ARRAY_FILTER_USE_BOTH);
        foreach ($participants_excel as $i_pe => $pe_i) {
            $data = [
                'year' => 2024,
                'matrix_point' => 9,
                'name' => $pe_i[1],
                'NRP' => $pe_i[2],
                'comp_pstn' => $pe_i[8],
                'target' => $pe_i[10],
                'score' => $pe_i[11],
            ];
            echo '<pre>', print_r($data, true);
            $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
            if (!$exist) {
                $success = $this->db->insert('comp_pstn_score', $data);
                echo '<pre>', print_r($success, true);
            }
        }
        $participants_excel = $this->session->userdata('participants_excel')[1];
        $participants_excel = array_filter($participants_excel, fn($pe_i, $i_pe) => $i_pe >= 4 && ($pe_i[3] || $pe_i[2]), ARRAY_FILTER_USE_BOTH);
        foreach ($participants_excel as $i_pe => $pe_i) {
            $data = [
                'year' => 2024,
                'matrix_point' => 9,
                'name' => $pe_i[1],
                'NRP' => $pe_i[2],
                'comp_pstn' => $pe_i[8],
                'target' => $pe_i[10],
                'score' => $pe_i[11],
            ];
            echo '<pre>', print_r($data, true);
            $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
            if (!$exist) {
                $success = $this->db->insert('comp_pstn_score', $data);
                echo '<pre>', print_r($success, true);
            }
        }
        $participants_excel = $this->session->userdata('participants_excel')[5];
        $participants_excel = array_filter($participants_excel, fn($pe_i, $i_pe) => $i_pe >= 4 && ($pe_i[3] || $pe_i[2]), ARRAY_FILTER_USE_BOTH);
        foreach ($participants_excel as $i_pe => $pe_i) {
            $data = [
                'year' => 2024,
                'matrix_point' => 9,
                'name' => $pe_i[1],
                'NRP' => $pe_i[2],
                'comp_pstn' => $pe_i[8],
                'target' => $pe_i[10],
                'score' => $pe_i[11],
            ];
            echo '<pre>', print_r($data, true);
            $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
            if (!$exist) {
                $success = $this->db->insert('comp_pstn_score', $data);
                echo '<pre>', print_r($success, true);
            }
        }
    }

    function import_finance()
    {
        $participants_excel = $this->session->userdata('participants_excel');

        foreach ($participants_excel as $i_sheet => $sheet_i) {
            $data['name'] = $sheet_i[3][2];
            $data['matrix_point'] = 14;
            $data['year'] = 2024;
            $scores = array_filter($sheet_i, fn($pe_i, $i_pe) => $i_pe >= 10 && $i_pe <= 20, ARRAY_FILTER_USE_BOTH);
            foreach ($scores as $i_score => $score_i) {
                $data['comp_pstn'] = $score_i[1];
                $data['target'] = 0;
                for ($i = 2; $i < 6; $i++) {
                    if ($score_i[$i]) {
                        $data['target'] = $i - 1;
                        break;
                    }
                }
                for ($i = 7; $i < 11; $i++) {
                    if ($score_i[$i]) {
                        $data['score'] = $i - 6;
                        break;
                    }
                }
                echo '<pre>', print_r($data, true);
                $exist = $this->db->get_where('comp_pstn_score', $data)->result_array();
                if (!$exist) {
                    $success = $this->db->insert('comp_pstn_score', $data);
                    echo '<pre>', print_r($success, true);
                }
            }
        }
    }
}
