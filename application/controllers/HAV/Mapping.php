<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mapping extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('competency/m_comp_lvl_assess', 'm_c_l_a'); // Adjust model name
    }

    public function index()
    {
        $data['employees'] = $this->m_c_l_a->get_comp_lvl_emp_assess();
        $data['title'] = "Human Asset Value Map";
        $data['content'] = "competency/HAV";
        $this->load->view('templates/header_footer', $data);
    }

    public function edit($NRP_hash, $method_id_hash = null)
    {
        if (!$method_id_hash) {
            if (!$this->input->post('tahun')) {
                $data['form_type'] = "choose_method";
                $data['NRP_hash'] = $NRP_hash;
                $data['methods'] = $this->db->get('comp_lvl_assess_method')->result_array();
            } else {
                $data['form_type'] = "edit_performance";
                $data['NRP_hash'] = $NRP_hash;
                $data['tahun'] = $this->input->post('tahun');
                $data['emp_ipa_score'] = $this->db->get_where('emp_ipa_score', ['md5(NRP)' => $NRP_hash, 'tahun' => $this->input->post('tahun')])->row_array();
            }
        } else {
            $data['form_type'] = "edit_potential";
            $employee = $this->m_c_l_a->get_comp_lvl_emp_assess($method_id_hash, "md5(e.NRP) = '$NRP_hash' AND md5(method_id)", false);
            if (!$employee) {
                $employee = $this->m_c_l_a->get_comp_lvl_emp_assess($NRP_hash, "md5(e.NRP)", false);
                $method = $this->db->get_where('comp_lvl_assess_method', ['md5(id)' => $method_id_hash])->row_array();
                $employee = array_merge($employee, array_fill_keys(
                    ['tahun', 'vendor', 'assess_score', 'recommendation'],
                    null
                ));

                $employee['method_id'] = $method['id'];
                $employee['method'] = $method['name'];
                $employee['score'] = [];
            }
            $data['employee'] = $employee;
            $data['comp_lvls'] = $this->db->get('comp_lvl')->result_array();
        }
        $data['NRP_hash'] = $NRP_hash;
        $data['method_id_hash'] = $method_id_hash;
        $data['content'] = "competency/HAV_edit";
        $this->load->view('templates/header_footer', $data);
    }

    public function submit($NRP_hash, $method_id_hash = null)
    {
        if ($this->input->post('type') == "potential") {
            if ($this->input->post('proceed') == "N") {
                redirect('HAV/mapping/edit/' . $NRP_hash);
            } elseif ($this->input->post('proceed') == "D") {
                $employee = $this->m_c_l_a->get_comp_lvl_emp_assess($method_id_hash, "md5(e.NRP) = '$NRP_hash' AND md5(method_id)", false);
                $this->db->where('comp_lvl_assess_id', $employee['comp_lvl_assess_id'])->delete('comp_lvl_assess_score');
                $this->db->where('id', $employee['comp_lvl_assess_id'])->delete('comp_lvl_assess');
                redirect('HAV/mapping/edit/' . $NRP_hash);
            } else {
                $success = $this->m_c_l_a->submit($NRP_hash);
                $this->session->set_flashdata('swal', [
                    'type' => $success ? 'success' : 'error',
                    'message' => $success ? 'Score Submitted Successfully' : 'Score Submit Failed'
                ]);
                redirect('HAV/mapping/edit/' . $NRP_hash);
            }
        } else {
            if ($this->input->post('proceed') == "N") {
                redirect('HAV/mapping/edit/' . $NRP_hash);
            } elseif ($this->input->post('proceed') == "D") {
                $this->db->where('md5(id)', $this->input->post('id_hash'))->delete('emp_ipa_score');
                redirect('HAV/mapping/edit/' . $NRP_hash);
            } else {
                $employee = $this->db->get_where('rml_sso_la.users', ['md5(NRP)' => $NRP_hash])->row_array();
                $data = [
                    'tahun' => $this->input->post('tahun'),
                    'score' => $this->input->post('score'),
                    'NRP' => $employee['NRP'],
                ];
                if ($this->input->post('id_hash')) {
                    $success = $this->db->where('md5(id)', $this->input->post('id_hash'))->update('emp_ipa_score', $data);
                } else {
                    $success = $this->db->insert('emp_ipa_score', $data);
                }
                $this->session->set_flashdata('swal', [
                    'type' => $success ? 'success' : 'error',
                    'message' => $success ? 'Score Submitted Successfully' : 'Score Submit Failed'
                ]);
                redirect('HAV/mapping/edit/' . $NRP_hash);
            }
        }
    }

    public function check()
    {
        $NRPS = ["10109071", "10109069", "10109078", "10109088", "10110115", "10110189", "10111264", "10111346", "10111350", "10111401", "10111551", "10112632", "10112853", "10117052", "10118002", "10109086", "10112726", "10112743", "10110177", "10110200", "10122289", "10113951", "10111394", "10111294", "10125097", "10112871", "10108056", "10112880"];
        $in_clause = "'" . implode("','", $NRPS) . "'";

        $query = $this->db->query("
            SELECT * FROM org_area_lvl_pstn_user oalpu
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            WHERE NRP IN($in_clause)
        ")->result_array();

        echo '<pre>', print_r($query, true);
        die;
    }

    public function import($sheet = 0)
    {
        ini_set('memory_limit', '3G');

        $this->load->model('competency/m_comp_level', 'm_c_l'); // Adjust model name
        $comp_lvl = $this->m_c_l->get_comp_level();

        $this->load->helper('conversion');
        $this->load->helper('extract_spreadsheet');
        $sheets = extract_spreadsheet('./uploads/imports_admin/hav_map_import.xlsx', true);
        $sheets = array_filter($sheets, fn($sheet_i, $i_sheet) => $i_sheet >= 5, ARRAY_FILTER_USE_BOTH);
        $data_assess_lvl_score = [];
        $data_assess_pstn = [];
        foreach ($sheets as $i_sheet => $sheet_i) {
            if (substr($sheet_i['name'], 3, 3) == "ALL" || substr($sheet_i['name'], 4, 3) == "ALL") {
                continue;
            }

            $columns = $sheet_i['rows'][1];

            $NRP_index = $this->find_partial_column_index($columns, 'nrp');
            $perscore_index = $this->find_partial_column_index($columns, 'skor untuk performance');
            $rekomendasi_index = $this->find_partial_column_index($columns, 'rekomendasi');
            $jenis_assessment_index = $this->find_partial_column_index($columns, 'JENIS ASESMEN');
            $vendor_assessment_index = $this->find_partial_column_index($columns, 'VENDOR ASSESSMEN');
            $tahun_assessment_index = $this->find_partial_column_index($columns, 'TAHUN ASSESSMEN');
            $jobfit_index = $this->find_partial_column_index($columns, 'job fit');
            $nilai_pa_index = $this->find_partial_column_labels($columns, 'nilai pa');
            $total_hasil_assessment_index = $this->find_partial_column_index($columns, 'TOTAL HASIL ASESMEN');

            if ($perscore_index === false) {
                echo "Index untuk skor performance tidak ditemukan";
                die;
            }

            // Cari batas akhir berdasarkan "kolom|column \d"
            $end_index = null;

            $comp_lvl_excel = [];

            for ($i = $perscore_index + 2; $i < count($columns); $i++) {
                $val = $columns[$i];

                if (!is_string($val) || trim($val) === '') continue;

                if (preg_match('/\b(kolom|column)\s*\d+/i', $val)) {
                    break;
                }

                $comp_lvl_excel[$i] = $val;
            }

            $comp_lvl_mapped = []; // hasil: [index_excel => comp_lvl_id]

            foreach ($comp_lvl_excel as $idx => $label_excel) {
                $normalized_excel = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '', $label_excel)));

                foreach ($comp_lvl as $comp) {
                    $normalized_comp = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '', $comp['name'])));

                    if (similar_text($normalized_excel, $normalized_comp, $percent) && $percent > 80) {
                        $comp_lvl_mapped[$idx] = $comp['id'];
                        break; // stop setelah cocok
                    }
                }

                if (!isset($comp_lvl_mapped[$idx])) {
                    echo "❌ Tidak ditemukan kecocokan untuk kolom: [$idx] $label_excel\n";
                    die;
                }
            }

            $rows = array_filter($sheet_i['rows'], fn($row_i, $i_row) => $i_row >= 2 && $row_i[$NRP_index], ARRAY_FILTER_USE_BOTH);
            foreach ($rows as $i_row => $row_i) {
                $tahun = $row_i[$tahun_assessment_index];
                $NRP = $row_i[$NRP_index];
                if ($tahun) {
                    $data_assessment = array(
                        "NRP" => $NRP,
                        "method_id" => $row_i[$jenis_assessment_index],
                        "tahun" => $tahun,
                        "vendor" => $row_i[$vendor_assessment_index],
                        "recommendation" => $row_i[$rekomendasi_index],
                        "score" => round($row_i[$jobfit_index], 2),
                    );

                    $this->db->insert('comp_lvl_assess', $data_assessment);
                    $id_assessment = $this->db->insert_id();

                    foreach ($comp_lvl_mapped as $i_clm => $clm_i) {
                        if ($row_i[$i_clm]) {
                            $data_assess_lvl_score[] = array(
                                "comp_lvl_assess_id" => $id_assessment,
                                "comp_lvl_id" => $clm_i,
                                "score" => $row_i[$i_clm],
                            );
                        }
                    }
                }

                foreach ($nilai_pa_index as $i_npi => $npi_i) {
                    if ($row_i[$i_npi]) {
                        $data_assess_pstn[] = array(
                            "NRP" => $NRP,
                            "tahun" => substr($npi_i, 9, 4),
                            "score" => round($row_i[$i_npi], 2),
                        );
                    }
                }
            }
        }
        echo '<pre>', var_dump("INSERT:");
        // echo '<pre>', var_dump($data_inserts);
        if ($data_assess_lvl_score) echo $this->db->insert_batch('comp_lvl_assess_score', $data_assess_lvl_score);
        if ($data_assess_pstn) echo $this->db->insert_batch('emp_ipa_score', $data_assess_pstn);
        echo '<pre>', var_dump("UPDATE:");
        // echo '<pre>', var_dump($data_updates);
        // if ($data_updates) echo $this->db->update_batch('comp_lvl_score', $data_updates, 'id');
        die;
    }

    function find_partial_column_index(array $columns, string $must_contain): int|false
    {
        foreach ($columns as $index => $value) {
            if (!is_string($value)) continue;

            $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $value)));

            if (stripos($normalized, $must_contain) !== false) {
                return $index;
            }
        }

        return false;
    }

    function find_partial_column_labels(array $columns, string $must_contain): array
    {
        $matches = [];

        foreach ($columns as $index => $value) {
            if (!is_string($value)) continue;

            // Normalisasi spasi dan lowercase untuk pencocokan
            $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $value)));

            if (stripos($normalized, $must_contain) !== false) {
                $matches[$index] = trim($value); // Simpan label asli
            }
        }

        return $matches;
    }
}
