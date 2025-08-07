<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_training_2024 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    function bulan_to_number($bulan)
    {
        // Daftar bulan dalam Bahasa Indonesia
        $bulan_map = [
            'JANUARI'   => '01',
            'FEBRUARI'  => '02',
            'MARET'     => '03',
            'APRIL'     => '04',
            'MEI'       => '05',
            'JUNI'      => '06',
            'JULI'      => '07',
            'AGUSTUS'   => '08',
            'SEPTEMBER' => '09',
            'OKTOBER'   => '10',
            'NOVEMBER'  => '11',
            'DESEMBER'  => '12'
        ];

        // Normalisasi input (hilangkan spasi dan ubah ke uppercase)
        $bulan = strtoupper(trim($bulan));

        // Cek apakah bulan valid
        return $bulan_map[$bulan] ?? null; // Return null jika tidak ditemukan
    }

    function convert_to_date_range($input, $year = null)
    {
        // Jika input null langsung return
        if (is_null($input)) {
            return ['start' => null, 'end' => null];
        }

        // Jika input angka (Excel serial date)
        if (is_numeric($input)) {
            $date = new DateTime('1899-12-30');
            $date->modify("+{$input} days");
            $ymd = $date->format('Y-m-d');
            return ['start' => $ymd, 'end' => $ymd];
        }

        // Normalisasi string
        $input = strtoupper(trim((string)$input));
        $input = preg_replace('/\s+/', ' ', $input);

        // Handle TBA
        if ($input === "TBA") {
            return ['start' => null, 'end' => null];
        }

        // Map bulan ID & EN (short + long + variasi)
        $bulan_map = [
            'JAN' => '01',
            'JANUARI' => '01',
            'FEB' => '02',
            'FEBRUARI' => '02',
            'MAR' => '03',
            'MARET' => '03',
            'APR' => '04',
            'APRIL' => '04',
            'MEI' => '05',
            'MAY' => '05',
            'JUN' => '06',
            'JUNI' => '06',
            'JUL' => '07',
            'JULI' => '07',
            'AGU' => '08',
            'AGS' => '08',
            'AGUSTUS' => '08',
            'AUG' => '08',
            'SEP' => '09',
            'SEPT' => '09',
            'SEPTEMBER' => '09',
            'OKT' => '10',
            'OKTOBER' => '10',
            'OCT' => '10',
            'NOV' => '11',
            'NOVEMBER' => '11',
            'DES' => '12',
            'DESEMBER' => '12',
            'DEC' => '12'
        ];

        // Regex 1: Range lintas bulan "20 Sep - 6 Okt"
        if (preg_match('/(\d{1,2})\s+([A-Z]+)\s*-\s*(\d{1,2})\s+([A-Z]+)/', $input, $matches)) {
            $day_start  = (int)$matches[1];
            $bulan1_txt = $matches[2];
            $day_end    = (int)$matches[3];
            $bulan2_txt = $matches[4];

            $bulan1 = $bulan_map[$bulan1_txt] ?? null;
            $bulan2 = $bulan_map[$bulan2_txt] ?? null;

            if (!$bulan1 || !$bulan2) return ['start' => null, 'end' => null];

            $year = $year ?? date('Y');
            $start_date = (new DateTime("{$year}-{$bulan1}-{$day_start}"))->format('Y-m-d');
            $end_date   = (new DateTime("{$year}-{$bulan2}-{$day_end}"))->format('Y-m-d');

            // Jika range lintas tahun (contoh: Des - Jan), naikkan tahun akhir
            if ($bulan2 < $bulan1) {
                $end_date = (new DateTime(($year + 1) . "-{$bulan2}-{$day_end}"))->format('Y-m-d');
            }

            return ['start' => $start_date, 'end' => $end_date];
        }

        // Regex 2: Range dalam bulan sama "16-19 Sep" atau single date "16 Sep"
        if (preg_match('/(\d{1,2})(?:-(\d{1,2}))?\s+([A-Z]+)/', $input, $matches)) {
            $day_start = (int)$matches[1];
            $day_end   = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : $day_start;
            $bulan_txt = $matches[3];
            $bulan     = $bulan_map[$bulan_txt] ?? null;

            if (!$bulan) return ['start' => null, 'end' => null];

            $year = $year ?? date('Y');
            $start_date = (new DateTime("{$year}-{$bulan}-{$day_start}"))->format('Y-m-d');
            $end_date   = (new DateTime("{$year}-{$bulan}-{$day_end}"))->format('Y-m-d');

            return ['start' => $start_date, 'end' => $end_date];
        }

        return ['start' => null, 'end' => null];
    }

    public function set_session_participants()
    {
        $this->load->helper(['conversion', 'extract_spreadsheet']);
        $data['participants'] = extract_spreadsheet('./uploads/imports_admin/ATMP 2024 import.xlsx')[2] ?? [];
        $this->session->set_userdata($data);
    }

    public function import_mts_2024()
    {
        $this->load->helper(['conversion', 'extract_spreadsheet']);
        $rows = extract_spreadsheet('./uploads/imports_admin/ATMP 2024 import.xlsx')[1] ?? [];
        $filtered = array_filter($rows, fn($v, $k) => $k >= 9 && !empty($v[2]), ARRAY_FILTER_USE_BOTH);

        foreach ($filtered as $i_trn => $trn_i) {
            $date_row = $rows[$i_trn + 1];
            echo '<pre>', print_r($trn_i, true);
            echo '<pre>', print_r($date_row, true);
            $year = 2024;
            $atmp = $this->db->get_where('trn_atmp', array('year' => $year, 'nama_program' => $trn_i[2]))->row_array();
            $data_i =  [
                'atmp_id' => $atmp['id'],
                'year' => $year,
                'nama_program' => $trn_i[2],
                'kategori_program' => $trn_i[3],
                'fasilitator' => $trn_i[4],
                'total_participants' => $trn_i[8],
                'actual_participants' => $trn_i[9],
                'grand_total' => currencyStringToInteger($trn_i[10]),
                'actual_budget' => currencyStringToInteger($trn_i[11]),
                'month' => indoMonthToNumber($trn_i[5]),
                'actual_month' => indoMonthToNumber($trn_i[6]),
                'start_date' => $this->convert_to_date_range($date_row[5], $year)['start'],
                'end_date' => $this->convert_to_date_range($date_row[5], $year)['end'],
                'actual_start_date' => $this->convert_to_date_range($date_row[6], $year)['start'],
                'actual_end_date' => $this->convert_to_date_range($date_row[6], $year)['end'],
            ];
            $data_i['status'] = 'P';
            if (indoMonthToNumber($trn_i[6])) $data_i['status'] = 'Y';
            // if (indoMonthToNumber($trn_i[5]) != indoMonthToNumber($trn_i[6])) $data_i['status'] = 'R';
            // if (!indoMonthToNumber($trn_i[6])) $data_i['status'] = 'N';
            $data[] = $data_i;
            echo '<pre>', print_r($data, true);
        }
        $this->db->insert_batch('trn_mts', $data);
    }

    public function import_mts_from_participants()
    {
        $rows = $this->session->userdata('participants');
        $participants = array_filter($rows, fn($prtc_i, $i_prtc) => $i_prtc >= 7 && ($prtc_i[2] || $prtc_i[3]), ARRAY_FILTER_USE_BOTH);
        $nama_programs = [];
        foreach ($participants as $key => $prtc) {
            $nama_programs[$key] = $prtc[7];
        }
        $nama_programs = array_unique($nama_programs);

        foreach ($nama_programs as $i_namprog => $namprog_i) {
            $mts = $this->db->get_where('trn_mts', array('LOWER(nama_program)' => strtolower($namprog_i[7]), 'year' => 2024))->row_array();
            if (!$mts) {
                $mts = $participants[$i_namprog];
                echo '<pre>', print_r($mts, true);
                $data['nama_program'] = $namprog_i[7];
                $data['nama_penyelenggara_fasilitator'] = $mts[8];
                $data['start_date'] = $this->convert_to_date_range($mts[10], 2024)['start'];
                $data['end_date'] = $this->convert_to_date_range($mts[11], 2024)['end'];
                $data['month'] = $this->bulan_to_number($mts[12]);
                $data['actual_start_date'] = $this->convert_to_date_range($mts[10], 2024)['start'];
                $data['actual_end_date'] = $this->convert_to_date_range($mts[11], 2024)['end'];
                $data['actual_month'] = $this->bulan_to_number($mts[12]);
                $data['year'] = $mts[13];
                $data['days'] = $mts[14];
                $data['hours'] = $mts[15];
                $data['total_hours'] = $mts[16];
                $data['sasaran_kompetensi'] = $mts[17];
                $data['kategori_program'] = $mts[19];
                $data['status'] = 'Y';
            }
        }
    }

    function mts_submit()
    {
        echo '<pre>', print_r($this->input->post(), true);
        die;
    }

    function mts_new($index)
    {
        $rows = $this->session->userdata('participants');
        $row = $rows[$index];

        $data['nama_program'] = $row[7];
        $data['nama_penyelenggara_fasilitator'] = $row[8];
        $data['start_date'] = $this->convert_to_date_range($row[10], 2024)['start'];
        $data['end_date'] = $this->convert_to_date_range($row[11], 2024)['end'];
        $data['month'] = $this->bulan_to_number($row[12]);
        $data['actual_start_date'] = $this->convert_to_date_range($row[10], 2024)['start'];
        $data['actual_end_date'] = $this->convert_to_date_range($row[11], 2024)['end'];
        $data['actual_month'] = $this->bulan_to_number($row[12]);
        $data['year'] = $row[13];
        $data['days'] = $row[14];
        $data['hours'] = $row[15];
        $data['total_hours'] = $row[16];
        $data['sasaran_kompetensi'] = $row[17];
        $data['kategori_program'] = $row[19];
        $data['status'] = 'Y';
        $success = $this->db->insert('trn_mts', $data);
        echo '<pre>', print_r($success, true);
        if ($success) {
            redirect('testing/import_participants_2024');
        }
    }

    function check_nrp_participants()
    {
        $rows = $this->session->userdata('participants');
        $participants = array_filter($rows, fn($prtc_i, $i_prtc) => $i_prtc >= 7 && ($prtc_i[2] || $prtc_i[3]), ARRAY_FILTER_USE_BOTH);
        $tmus = $this->db->get('trn_mts_user')->result_array();
        $users = $this->db->get('rml_sso_la.users')->result_array();
        $nrps = array_filter($participants, fn($prtc_i, $i_prtc) => $prtc_i[2], ARRAY_FILTER_USE_BOTH);
        $names = array_filter($participants, fn($prtc_i, $i_prtc) => !$prtc_i[2] && $prtc_i[3], ARRAY_FILTER_USE_BOTH);
        $nrp_exc = ['RAL', '0123005'];
        $i = 1;
        foreach ($nrps as $i_nrp => $nrp_i) {
            if (in_array($nrp_i[2], $nrp_exc)) continue;
            if ($nrp_i[3] == 'Rodolvus Aldo Lepe ') $nrp_i[2] = '11224004';
            // echo '<pre>', print_r($i++ . ' - ' . $nrp_i[2], true);
            $user_filtered = array_filter($users, fn($user_i, $i_user) => $user_i['NRP'] == $nrp_i[2], ARRAY_FILTER_USE_BOTH);
            $nrp = null;
            if (count($user_filtered) != 1) {
                // echo '<pre>', print_r("nrp failed", true);
                $user_filtered = array_filter($users, fn($user_i, $i_user) => $user_i['NRP'] == '1' . $nrp_i[2], ARRAY_FILTER_USE_BOTH);
                if (count($user_filtered) != 1) {
                    // echo '<pre>', print_r("1 . nrp failed", true);
                    $user_filtered = array_filter($users, fn($user_i, $i_user) => $user_i['NRP'] == '10' . $nrp_i[2], ARRAY_FILTER_USE_BOTH);
                    if (count($user_filtered) != 1) {
                        // echo '<pre>', print_r("10 . nrp failed", true);
                        $user_filtered = array_filter($users, fn($user_i, $i_user) => $user_i['NRP'] == '1' . substr($nrp_i[2], 2), ARRAY_FILTER_USE_BOTH);
                        if (count($user_filtered) != 1) {
                            // echo '<pre>', print_r("remove 2 and add 1 . nrp failed", true);
                            $user_filtered = array_filter($users, fn($user_i, $i_user) => strtolower($user_i['FullName']) == strtolower($nrp_i[3]), ARRAY_FILTER_USE_BOTH);
                            if (count($user_filtered) != 1) {
                                // echo '<pre>', print_r('name failed', true);
                                $user_filtered = array_filter($users, fn($user_i, $i_user) => strtolower($user_i['FullName']) == strtolower($nrp_i[3]) && $user_i['NRP'] == '1' . $nrp_i[2], ARRAY_FILTER_USE_BOTH);
                                if (count($user_filtered) != 1) {
                                    echo '<pre>', print_r('nrp & name failed', true);
                                    echo '<pre>', print_r($nrp_i, true);
                                    echo '<pre>', print_r($user_filtered, true);
                                    die;
                                } else {
                                    $user_filtered = array_values($user_filtered);
                                    $nrp = $user_filtered[0]['NRP'];
                                }
                            } else {
                                $user_filtered = array_values($user_filtered);
                                $nrp = $user_filtered[0]['NRP'];
                            }
                        } else {
                            $user_filtered = array_values($user_filtered);
                            $nrp = $user_filtered[0]['NRP'];
                        }
                    } else {
                        $user_filtered = array_values($user_filtered);
                        $nrp = $user_filtered[0]['NRP'];
                    }
                } else {
                    $user_filtered = array_values($user_filtered);
                    $nrp = $user_filtered[0]['NRP'];
                }
            } else {
                $user_filtered = array_values($user_filtered);
                $nrp = $user_filtered[0]['NRP'];
            }
            // update session here
            // $participants = $this->session->userdata('participants');
            // $user = $this->db->get_where('rml_sso_la.users', array('NRP' => $nrp))->row_array();
            // $ptcp_edit = array_filter($participants, fn($ptcp_i, $i_ptcp) => $ptcp_i[2] == $nrp_i[2], ARRAY_FILTER_USE_BOTH);
            // foreach ($ptcp_edit as $i_ptcpe => $ptcpe_i) {
            //     $participants[$i_ptcpe][2] = $user['NRP'];
            //     $participants[$i_ptcpe][3] = $user['FullName'];
            // }
            // $this->session->set_userdata('participants', $participants);
            // echo '<pre>', print_r($nrp, true);

            $mts = $this->db->get_where('trn_mts', array('nama_program' => $nrp_i[7], 'year' => 2024))->row_array();
            if ($mts) {
                $mts_user = $this->db->get_where('trn_mts_user', array('mts_id' => $mts['id'], 'NRP' => $nrp))->row_array();
                if (!$mts_user) {
                    $data = [
                        'NRP' => $nrp,
                        'mts_id' => $mts['id'],
                    ];
                    $success = $this->db->insert('trn_mts_user', $data);
                    if (!$success) {
                        echo '<pre>', print_r($data, true);
                        die;
                    }
                }
            } else {
                echo '<pre>', print_r($nrp_i, true);
                die;
            }
        }

        echo '<pre>', print_r('---------------------------------', true);

        foreach ($names as $i_name => $name_i) {
            $nrp = null;
            if ($name_i[3] == 'Roy Marbun') continue;
            if ($name_i[3] == 'Tim Krassi') continue;
            if ($name_i[3] == 'Tim CSR RMGM') continue;
            if ($name_i[3] == 'Muhammad Roni') continue;
            if ($name_i[3] == 'Rizki') continue;
            if ($name_i[3] == 'Roni ') continue;
            if ($name_i[3] == 'Roni') continue;
            if ($name_i[3] == 'Ilham') continue;
            if ($name_i[3] == 'Malik Maulana Anwar') continue;
            if ($name_i[3] == 'Fadli Muhammad ') continue;
            if ($name_i[3] == 'Ade') continue;

            if ($name_i[3] == 'Purwanto') $nrp = '10207216';
            if ($name_i[3] == 'Suwardi') $nrp = '11112019';
            if ($name_i[3] == 'Wahid ') $nrp = '10718143';
            if ($name_i[3] == 'Slamet Riyadi') $nrp = '11110048';
            if ($name_i[3] == 'Setyawan Jodi') $nrp = '11523020';
            if ($name_i[3] == 'Pemiswandi') $nrp = '70124008';
            if ($name_i[3] == 'Arief Kurniawan') $nrp = '10112569';
            if ($name_i[3] == 'Adi Setiawan ') $nrp = '11224007';
            if ($name_i[3] == 'Agung Paninti') $nrp = '11224010';
            if ($name_i[3] == 'Khairil Pahmi') $nrp = '11224011';
            if ($name_i[3] == 'Alvin') $nrp = '81223024';
            if ($name_i[3] == 'Melky Pratama ') $nrp = '11224014';
            if ($name_i[3] == 'Aprianoor') $nrp = '11224015';
            if ($name_i[3] == 'Andi Saputra Nong Lukas ') $nrp = '11224016';
            if ($name_i[3] == 'Robert Benson ') $nrp = '11224017';
            if ($name_i[3] == 'AHMAD JERI') $nrp = '11224018';
            if ($name_i[3] == 'MUHAMAD ALFARIZI') $nrp = '11224012';
            if ($name_i[3] == 'Oktavian Valentino') $nrp = '11224009';

            // echo '<pre>', print_r($i++ . ' - ' . $name_i[3], true);
            $user_filtered = array_filter($users, fn($user_i, $i_user) => strtolower($user_i['FullName']) == strtolower($name_i[3]), ARRAY_FILTER_USE_BOTH);
            if (count($user_filtered) != 1) {
                // echo '<pre>', print_r('name failed', true);
                $user_filtered = array_filter($users, fn($user_i) => $this->match_initial($user_i['FullName'], $name_i[3]));
                if (count($user_filtered) != 1) {
                    // echo '<pre>', print_r('name & initials failed', true);
                    // echo '<pre>', print_r($name_i[3], true);
                    // echo '<pre>', print_r(array_column($user_filtered, 'FullName'), true);
                } else {
                    $user_filtered = array_values($user_filtered);
                    $nrp = $user_filtered[0]['NRP'];
                }
            } else {
                $user_filtered = array_values($user_filtered);
                $nrp = $user_filtered[0]['NRP'];
            }

            // echo '<pre>', print_r($name_i, true);
            $mts = $this->db->get_where('trn_mts', array('nama_program' => $nrp_i[7], 'year' => 2024))->row_array();

            if (!$nrp) {
                echo '<pre>', print_r($name_i, true);
                $html = '<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <title>Form Select2</title>
                        <!-- jQuery -->
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <!-- Select2 CSS & JS -->
                        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
                        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                    </head>
                    <body>
                    ';

                $html .= '<h3>Pilih Program untuk: ' . htmlspecialchars($name_i[3]) . '</h3>
                    <form method="post" action="' . site_url('testing/mts_ptcp') . '">
                    <input type="hidden" name="index_name" value="' . $i_name . '">
                        <select name="NRP" class="select2" style="width:70%;">
                            <option value="">-- Pilih Program --</option>';
                foreach ($users as $i_user => $user_i) {
                    $html .= '<option value="' . $user_i['NRP'] . '">' . $user_i['NRP'] . " - " . $user_i['PSubarea'] . " - " . $user_i['EmployeeGroup'] . ' - ' . htmlspecialchars($user_i['FullName']) . " - " . $user_i['OrgUnitName'] . '</option>';
                }
                $html .= '</select>
                        <button type="submit">Simpan</button>
                    </form><hr>';

                // Inisialisasi Select2
                $html .= '
                    <script>
                    $(document).ready(function() {
                        var select = $(".select2").select2();

                        // Buka Select2 setelah sedikit delay agar DOM siap
                        setTimeout(function() {
                            select.select2("open");
                        }, 200);

                        // Paksa fokus ke search input tiap kali dropdown terbuka
                        $(document).on("select2:open", () => {
                            let searchField = document.querySelector(".select2-container--open .select2-search__field");
                            if (searchField) {
                                searchField.focus();
                            }
                        });
                    });
                    </script>
                </body>
                </html>';

                echo $html;
                die;
            } else {
                if ($mts) {
                    $mts_user = $this->db->get_where('trn_mts_user', array('mts_id' => $mts['id'], 'NRP' => $nrp))->row_array();
                    if (!$mts_user) {
                        $data = [
                            'NRP' => $nrp,
                            'mts_id' => $mts['id'],
                        ];
                        $success = $this->db->insert('trn_mts_user', $data);
                        if (!$success) {
                            echo '<pre>', print_r($data, true);
                            die;
                        }
                    }
                } else {
                    echo '<pre>', print_r($nrp_i, true);
                    die;
                }
            }
            // update session here
            // $participants = $this->session->userdata('participants');
            // $user = $this->db->get_where('rml_sso_la.users', array('NRP' => $nrp))->row_array();
            // $ptcp_edit = array_filter($participants, fn($ptcp_i, $i_ptcp) => $ptcp_i[3] == $name_i[3], ARRAY_FILTER_USE_BOTH);
            // foreach ($ptcp_edit as $i_ptcpe => $ptcpe_i) {
            //     $participants[$i_ptcpe][2] = $user['NRP'];
            //     $participants[$i_ptcpe][3] = $user['FullName'];
            // }
            // $this->session->set_userdata('participants', $participants);
            // echo '<pre>', print_r($nrp, true);
        }
    }

    function import_participants()
    {
        $rows = $this->session->userdata('participants');
        $participants = array_filter($rows, fn($prtc_i, $i_prtc) => $i_prtc >= 7 && $prtc_i[2], ARRAY_FILTER_USE_BOTH);
        foreach ($participants as $i_ptcp => $ptcp_i) {
            $mts = $this->db->get_where('trn_mts', array('LOWER(nama_program)' => strtolower($ptcp_i[7]), 'year' => 2024))->row_array();
            if ($mts) {
                $data = [
                    'NRP' => $ptcp_i[2],
                    'mts_id' => $mts['id'],
                ];
                $mts_user = $this->db->get_where('trn_mts_user', $data)->row_array();
                if (!$mts_user) {
                    $this->db->insert('trn_mts_user', $data);
                }
            }
        }
    }

    function match_initial($candidate_name, $input_name)
    {
        $input_parts = preg_split('/\s+/', strtoupper(trim($input_name)));
        $candidate_parts = preg_split('/\s+/', strtoupper(trim($candidate_name)));

        // Kalau kandidat punya kata lebih sedikit dari input, langsung false
        if (count($candidate_parts) < count($input_parts)) {
            return false;
        }

        foreach ($input_parts as $i => $part) {
            if (strlen($part) === 1) {
                // Kalau 1 huruf, cek apakah inisial cocok
                if (substr($candidate_parts[$i], 0, 1) !== $part) {
                    return false;
                }
            } else {
                // Kalau bukan inisial, cek harus sama persis
                if ($candidate_parts[$i] !== $part) {
                    return false;
                }
            }
        }

        return true;
    }

    function mts_ptcp()
    {
        echo '<pre>', print_r($this->input->post(), true);
        $participants = $this->session->userdata('participants');
        $ptcp = $participants[$this->input->post('index_name')];
        $user = $this->db->get_where('rml_sso_la.users', array('NRP' => $this->input->post('NRP')))->row_array();
        $ptcp_edit = array_filter($participants, fn($ptcp_i, $i_ptcp) => $ptcp_i[3] == $ptcp[3], ARRAY_FILTER_USE_BOTH);
        foreach ($ptcp_edit as $i_ptcpe => $ptcpe_i) {
            $participants[$i_ptcpe][2] = $user['NRP'];
            $participants[$i_ptcpe][3] = $user['FullName'];
        }
        $this->session->set_userdata('participants', $participants);
        redirect('testing/import_participants');
    }

    function session()
    {
        $participants = $this->session->userdata('participants');

        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<thead>";
        echo "<tr><th>NRP</th><th>Nama</th></tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($participants as $ptcp_i) {
            echo "<tr>";
            echo "<td>{$ptcp_i[2]}</td>";
            echo "<td>{$ptcp_i[3]}</td>";
            // foreach ($ptcp_i as $i_col => $col_i) {
            //     echo "<td>";
            //     echo $col_i;
            //     echo "</td>";
            // }
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        die;
    }
}
