<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_training_previous extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function set_session_excel()
    {
        $this->load->helper(['conversion', 'extract_spreadsheet']);
        $data['participants'] = extract_spreadsheet('./uploads/imports_admin/ATMP previous import.xlsx')[0] ?? [];
        $data['skip_names'] = [];
        $success = $this->session->set_userdata($data);
        echo '<pre>', print_r('done', true);
        die;
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

        // ✅ Tambahan: Deteksi format DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $input, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];

            $date = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
            if ($date) {
                $ymd = $date->format('Y-m-d');
                return ['start' => $ymd, 'end' => $ymd];
            }
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

    function check_nrp_peserta($year)
    {
        $rows = $this->session->userdata('participants');
        $participants = array_filter($rows, fn($prtc_i, $i_prtc) => $i_prtc >= 2 && ($prtc_i[2] || $prtc_i[3]) && $prtc_i[16] == $year, ARRAY_FILTER_USE_BOTH);
        $tmus = $this->db->get('trn_mts_user')->result_array();
        $users = $this->db->get('rml_sso_la.users')->result_array();
        $nrps = array_filter($participants, fn($prtc_i, $i_prtc) => $prtc_i[2], ARRAY_FILTER_USE_BOTH);
        $names = array_filter($participants, fn($prtc_i, $i_prtc) => !$prtc_i[2] && $prtc_i[3], ARRAY_FILTER_USE_BOTH);
        $nrp_exc = ['RAL', '0123005'];
        $i = 1;

        // === Proses NRP Match ===
        foreach ($nrps as $i_nrp => $nrp_i) {
            if (in_array($nrp_i[2], $nrp_exc)) continue;
            if (in_array($nrp_i[3], $this->session->userdata('skip_names'))) continue;
            if ($nrp_i[3] == 'Rodolvus Aldo Lepe ') $nrp_i[2] = '11224004';

            $nrp = $this->find_nrp($users, $nrp_i[2], $nrp_i[3]);
            if ($nrp) {
                // Update session
                $participants = $this->session->userdata('participants');
                $user = $this->db->get_where('rml_sso_la.users', ['NRP' => $nrp])->row_array();
                foreach ($participants as $idx => $ptcp) {
                    if ($ptcp[2] == $nrp_i[2]) {
                        $participants[$idx][2] = $user['NRP'];
                        $participants[$idx][3] = $user['FullName'];
                    }
                }
                $this->session->set_userdata('participants', $participants);
            }
        }

        echo '<pre>', print_r('---------------------------------', true);

        // === Proses Name Match & Kumpulkan yang gagal ===
        $unmatched_names = [];
        foreach ($names as $i_name => $name_i) {
            if ($name_i[3] == 'MEKANIK') continue;
            if (in_array($name_i[3], $this->session->userdata('skip_names'))) continue;

            $nrp = $this->find_nrp_by_name($users, $name_i[3]);
            if (!$nrp) {
                $unmatched_names[$i_name] = $name_i;
            } else {
                // Update session kalau ketemu
                $participants = $this->session->userdata('participants');
                $user = $this->db->get_where('rml_sso_la.users', ['NRP' => $nrp])->row_array();
                foreach ($participants as $idx => $ptcp) {
                    if ($ptcp[3] == $name_i[3]) {
                        $participants[$idx][2] = $user['NRP'];
                        $participants[$idx][3] = $user['FullName'];
                    }
                }
                $this->session->set_userdata('participants', $participants);
            }
        }

        // === Render Bulk Form kalau ada unmatched ===
        if (!empty($unmatched_names)) {
            $this->render_bulk_form($unmatched_names, $users, $year);
            return; // Stop disini biar tidak lanjut otomatis
        }

        echo "Semua peserta sudah berhasil dicocokkan!";
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

    private function find_nrp($users, $nrp_raw, $fullname)
    {
        $patterns = [
            $nrp_raw,
            '1' . $nrp_raw,
            '10' . $nrp_raw,
            '1' . substr($nrp_raw, 2)
        ];

        foreach ($patterns as $pattern) {
            $filtered = array_filter($users, fn($u) => $u['NRP'] == $pattern);
            if (count($filtered) == 1) {
                return array_values($filtered)[0]['NRP'];
            }
        }

        // Coba match berdasarkan nama
        $filtered = array_filter($users, fn($u) => strtolower($u['FullName']) == strtolower($fullname));
        if (count($filtered) == 1) return array_values($filtered)[0]['NRP'];

        // Coba kombinasi nama + NRP variasi
        $filtered = array_filter($users, fn($u) => strtolower($u['FullName']) == strtolower($fullname) && $u['NRP'] == '1' . $nrp_raw);
        return (count($filtered) == 1) ? array_values($filtered)[0]['NRP'] : null;
    }

    private function find_nrp_by_name($users, $fullname)
    {
        $filtered = array_filter($users, fn($u) => strtolower($u['FullName']) == strtolower($fullname));
        if (count($filtered) == 1) return array_values($filtered)[0]['NRP'];

        $filtered = array_filter($users, fn($u) => $this->match_initial($u['FullName'], $fullname));
        return (count($filtered) == 1) ? array_values($filtered)[0]['NRP'] : null;
    }

    private function render_bulk_form($unmatched_names, $users, $year)
    {
        echo '<!DOCTYPE html><html><head>
            <meta charset="UTF-8"><title>Bulk Select</title>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        </head><body>';

        echo '<h3>Pilih NRP untuk Peserta Berikut:</h3>';
        echo '<form method="post" action="' . site_url('dummy/import_training_previous/submit_participant/' . $year) . '">';

        foreach ($unmatched_names as $i_name => $name_i) {
            echo '<div style="margin-bottom:20px">';
            foreach ($name_i as $i_col => $col_i) {
                echo '<label><b>' . $col_i . '</b></label><br>';
            }
            echo '<input type="hidden" name="index_name[]" value="' . $i_name . '">';
            echo '<select name="NRP[' . $i_name . ']" class="select2" style="width:70%;">';
            echo '<option value="">-- Pilih NRP --</option>';
            foreach ($users as $user_i) {
                echo '<option value="' . $user_i['NRP'] . '">' . $user_i['NRP'] . ' - ' . $user_i['PSubarea'] . ' - ' . $user_i['EmployeeGroup'] . ' - ' . htmlspecialchars($user_i['FullName']) . ' - ' . $user_i['OrgUnitName'] . '</option>';
            }
            echo '<input type="checkbox" name="skip[' . $i_name . ']">';
            echo '<label>skip this name</label>';
            echo '</div>';
        }

        echo '<button type="submit">Simpan Semua</button></form>';
        echo '<script>$(document).ready(function(){$(".select2").select2();});</script></body></html>';
        die;
    }

    function submit_participant($year)
    {
        $participants = $this->session->userdata('participants');
        $nrps = array_filter($this->input->post('NRP'), fn($nrp_i, $i_nrp) => $nrp_i, ARRAY_FILTER_USE_BOTH);
        $skip_names = $this->input->post('skip');
        foreach ($nrps as $i_nrp => $nrp_i) {
            $ptcp = $participants[$i_nrp];
            $user = $this->db->get_where('rml_sso_la.users', array('NRP' => $nrp_i))->row_array();
            $ptcp_edit = array_filter($participants, fn($ptcp_i, $i_ptcp) => $ptcp_i[3] == $ptcp[3], ARRAY_FILTER_USE_BOTH);
            foreach ($ptcp_edit as $i_ptcpe => $ptcpe_i) {
                $participants[$i_ptcpe][2] = $user['NRP'];
                $participants[$i_ptcpe][3] = $user['FullName'];
            }
        }

        foreach ($skip_names as $i_skinam => $skinam_i) {
            if ($skinam_i == 'on') {
                $skip_names = $this->session->userdata('skip_names');
                array_push($skip_names, $participants[$i_skinam][3]);
                $this->session->set_userdata('skip_names', $skip_names);
            }
        }
        $this->session->set_userdata('participants', $participants);
        redirect('dummy/import_training_previous/check_nrp_peserta/' . $year);
    }

    function skip_participant($name, $year)
    {
        $skip_names = $this->session->userdata('skip_names');
        array_push($skip_names, urldecode($name));
        $this->session->set_userdata('skip_names', $skip_names);
        redirect('dummy/import_training_previous/check_nrp_peserta/' . $year);
    }

    function session()
    {
        $participants = $this->session->userdata('participants');
        // $participants = $this->session->userdata('skip_names');

        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>NRP</th>";
        echo "<th>Nama</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($participants as $ptcp_i) {
            // echo "<tr>";
            // echo "<td>{$ptcp_i}</td>";
            // echo "</tr>";
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
