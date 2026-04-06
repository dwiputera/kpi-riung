<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_tna_2026 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('employee/M_employee', 'm_emp');
    }

    function import()
    {
        $names = [
            'Edi Pranoto' => '10118040',
            'SITI FADHILAH BAIZA' => '10122316',
            'Achmad Rizqi' => '10119003',
            'Heri Susanto' => '10111423',
            'Totok Murdianto' => '10411450',
            'Vanya Cesaria Evelina' => '10124129',
            'Reki Patlianor' => '11522137',
            'Fadhli Muhammad' => '11522122',
            'Faisal Majid' => '11522133',
            'SOLIKHIN' => '11111103',
            'KARTIKO WIBOWO' => '10121135',
            'ANGGIA PUTRA N' => '10121093',
            'ADITYA CIPTA UTAMA' => '10111410',
            'FANI DWI CAHYONO' => '10121201',
            'EKO WIJAYA' => '11222197',
            'GOLDY PUTRA S.' => '10121197',
            'EKO SUDANTOKO' => '11110061',
            'JHON FADLI' => '11410030',
            'AGUS SUBAGYO' => '11109085',
            'SARYONO' => '10124266',
            'M MUHTAR NUGROHO' => '11110073',
            'NUR MUHADI' => '11112013',
            'HENDRY SETYAWAN' => '11112024',
            'Edi Dia Ghozali marselo' => '11112039',
            'WIYOGO' => '11110051',
        ];

        $nrp_not_found = [];

        $year = 2026;
        $lnas = json_decode(file_get_contents(__DIR__ . '/import_tna_2026.json'), true);

        $lnas_grouped = [];

        foreach ($lnas as $row) {
            $nrp  = $row['NRP'];
            $comp = $row['Nama Kompetensi'];

            if (!isset($lnas_grouped[$nrp])) {
                $lnas_grouped[$nrp] = [
                    'Nama'             => $row['Nama'],
                    'NRP'              => $row['NRP'],
                    'Jabatan'          => $row['Jabatan'],
                    'Kelompok Jabatan' => $row['Kelompok Jabatan'],
                    'Departemen'       => $row['Departemen'],
                    'Site'             => $row['Site'],
                    'Kompetensi'       => []
                ];
            }

            $lnas_grouped[$nrp]['Kompetensi'][$comp] = [
                'plan'   => $row['Plan']   !== '' ? (int)$row['Plan']   : null,
                'actual' => $row['Actual'] !== '' ? (int)$row['Actual'] : null,
                'gap'    => $row['GAP']    !== '' ? (int)$row['GAP']    : null,
            ];
        }

        foreach ($lnas_grouped as $i_lnag => $lnag_i) {
            if (in_array($lnag_i['Nama'], ['Webi Febrian', 'Alexander Rendra Pratama', 'SIGIT PREBIANTO'])) continue;
            $user = $this->db->get_where('rml_sso_la.users', ['NRP' => $lnag_i['NRP']])->row_array();
            if (!$user) {
                $user = $this->db->get_where('rml_sso_la.users', ['NRP' => '10' . $lnag_i['NRP']])->row_array();
                if ($user) {
                    $lnas_grouped[$i_lnag]['NRP'] = '10' . $lnag_i['NRP'];
                } else {
                    $nrp_not_found[] = $lnag_i['Nama'];
                    $user = $this->db->get_where('rml_sso_la.users', ['NRP' => $names[$lnag_i['Nama']]])->row_array();
                    if ($user) {
                        $lnas_grouped[$i_lnag]['NRP'] = $names[$lnag_i['Nama']];
                    } else {
                        echo '<pre>', print_r($lnag_i, true);
                        die;
                    }
                }
            }
        }

        foreach ($lnas_grouped as $i_lnag => $lnag_i) {
            if (in_array($lnag_i['Nama'], ['Webi Febrian', 'Alexander Rendra Pratama', 'SIGIT PREBIANTO'])) continue;
            $pstn = $this->db->get_where('org_area_lvl_pstn_user', ['NRP' => $lnag_i['NRP']])->row_array();
            if (!$pstn) {
                echo '<pre>', print_r($lnag_i, true);
            }
        }

        $this->load->model('organization/m_user');
        $this->load->model('organization/m_position');
        foreach ($lnas_grouped as $i_lnag => $lnag_i) {
            if (in_array($lnag_i['Nama'], ['Webi Febrian', 'Alexander Rendra Pratama', 'SIGIT PREBIANTO'])) continue;
            $user = $this->m_user->get_area_lvl_pstn_user($lnag_i['NRP'], 'NRP', false);
            $mp_id = $lnag_i['Departemen'];
            if (!$mp_id) {
                $matrix_point = $this->m_position->get_area_lvl_pstn($user['oalp_id'], 'oalp.id', false);
                $mp_id = $matrix_point['mp_id'];
            }
            $comp_positions = $this->db->get_where('comp_position', ['area_lvl_pstn_id' => $mp_id])->result_array();
            foreach ($lnag_i['Kompetensi'] as $i_komp => $komp_i) {
                if ($mp_id == 50 && $i_komp == 'Risk Management') continue;
                if ($mp_id == 50 && $i_komp == 'Corporate Finance') continue;
                if ($mp_id == 50 && $i_komp == 'Project Cost & Evaluation') continue;
                if ($mp_id == 50 && $i_komp == 'Operational Management') continue;
                $found = null;

                foreach ($comp_positions as $row) {
                    if (
                        strtolower(trim($row['name'])) ===
                        strtolower(trim($i_komp))
                    ) {
                        $found = $row;
                        $comp_pstn_score = $this->db->get_where('comp_pstn_score', ['year' => $year, 'NRP' => $lnag_i['NRP'], 'comp_pstn_id' => $found['id']])->row_array();
                        $data = [
                            'year' => $year,
                            'NRP' => $lnag_i['NRP'],
                            'comp_pstn_id' => $found['id'],
                            'score' => $komp_i['actual'],
                        ];
                        if ($comp_pstn_score) {
                            $this->db->where('id', $comp_pstn_score['id'])->update('comp_pstn_score', $data);
                        } else {
                            $this->db->insert('comp_pstn_score', $data);
                        }
                        break;
                    }
                }
                if (!$found) {
                    echo '<pre>', print_r($user, true);
                    echo '<pre>', print_r($mp_id, true);
                    echo '<pre>', print_r($lnag_i, true);
                    echo '<pre>', print_r($comp_positions, true);
                    echo '<pre>', print_r($i_komp, true);
                    die;
                }
            }
        }
    }
}
