<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ATMP extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('training/m_atmp');
    }

    function index()
    {
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $data['atmps'] = $this->m_atmp->get($year);
        $data['trainings'] = $this->m_atmp->get_training($year);
        $data['chart'] = $this->m_atmp->get_training_chart($year);
        $data['year'] = $year;
        $data['content'] = "training/ATMP";
        $this->load->view('templates/header_footer', $data);
    }

    function do_upload()
    {
        $config['upload_path'] = './uploads/temp/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 2048; // max size in KB

        // Step 1: Validate text input
        $this->form_validation->set_rules('year', 'Year', 'required|numeric|exact_length[4]|greater_than_equal_to[1900]|less_than_equal_to[2100]');


        if ($this->form_validation->run() == FALSE) {
            redirect('training/atmp');
            return;
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => strip_tags($error)
            ]);
            redirect('training/atmp?year=' . $this->input->post('year'));
            return;
        } else {
            $upload_data = $this->upload->data();
            $original_name = $upload_data['client_name']; // Original file name
            $file_ext = $upload_data['file_ext']; // .xls or .xlsx

            $old_atmp_docs = $this->db->where('year', $this->input->post('year'))->get('trn_atmp_docs')->row_array();
            // Insert record into DB without final filename
            $this->load->database();
            $this->db->insert('trn_atmp_docs', [
                'file_name' => $original_name,
                'year' => $this->input->post("year"),
            ]);
            $insert_id = $this->db->insert_id();

            // New file name using ID
            $new_filename = $insert_id . $file_ext;
            $new_path = './uploads/ATMP/' . $new_filename;

            // Rename the file
            rename($upload_data['full_path'], $new_path);

            $insert_data = $this->array_from_excel($new_path);
            if ($insert_data['status'] == 'error') {
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => $insert_data['message'],
                ]);
                unlink($new_path);
                $this->db->where('id', $insert_id)->delete('trn_atmp_docs');
                redirect('training/atmp');
                return;
            }

            if ($old_atmp_docs) {
                $this->db->where('id', $old_atmp_docs['id'])->delete('trn_atmp_docs');
                $this->db->where('year', $this->input->post('year'))->delete('trn');
                $old_atmp_docs_check = $this->check_file(md5($old_atmp_docs['id']));
                if ($old_atmp_docs_check['status'] == "OK") unlink($old_atmp_docs["file_path"]);
            }
            $this->db->insert_batch('trn', $insert_data['atmps']);

            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "File uploaded successfully"
            ]);

            redirect("training/ATMP?year=" . $this->input->post('year'));
            return;
        }
    }

    function reformat_date($d)
    {
        // Lewati jika formula Excel
        if (strpos($d, '=') === 0) {
            echo "SKIPPED: $d\n";
            return null;
        }

        if (is_numeric($d)) {
            // Serial date Excel
            $date = (new DateTime('1899-12-30'))->modify("+{$d} days");
        } else {
            $parts = explode('/', $d);

            // Perbaiki tahun jika pendek (0204 -> 2024)
            if (isset($parts[2]) && $parts[2] < 1000) {
                $parts[2] = str_pad($parts[2], 4, '20', STR_PAD_LEFT);
            }

            // Tentukan format (d/m/Y atau m/d/Y)
            if ($parts[0] > 12) {
                $date = DateTime::createFromFormat('d/m/Y', implode('/', $parts));
            } else {
                $date = DateTime::createFromFormat('m/d/Y', implode('/', $parts));
            }
        }

        return $date->format('Y-m-d') . PHP_EOL;
    }

    function array_from_excel($file_path)
    {
        $this->load->helper('conversion');
        $this->load->helper('extract_spreadsheet');
        $sheets = extract_spreadsheet($file_path);
        $rows = $sheets[0];
        $trns = array_filter($rows, fn($value, $key) => $key >= 8 && $value[2] != null, ARRAY_FILTER_USE_BOTH);
        $trn_data = [];
        $data['status'] = "OK";

        foreach ($trns as $row => $trn) {
            $date_start = $trn[15] ? $this->reformat_date($trn[15]) : null;
            $date_end = $trn[16] ? $this->reformat_date($trn[16]) : null;
            if ($date_start == '1970-01-01') {
                $column = numberToExcelColumn(5);
                $data['status'] = 'error';
                $data['message'] = "error in cell $column" . $row + 1;
                return $data;
            }
            if ($date_end == '1970-01-01') {
                $column = numberToExcelColumn(6);
                $data['status'] = 'error';
                $data['message'] = "error in cell $column" . $row + 1;
                return $data;
            }

            $trn_data[] = [
                'year' => $this->input->post("year"),
                'departemen_pengampu' => $trn[1],
                'nama_program' => $trn[2],
                'batch' => $trn[3],
                'jenis_kompetensi' => $trn[4],
                'sasaran_kompetensi' => $trn[5],
                'level_kompetensi' => $trn[6],
                'target_peserta' => $trn[7],
                'staff_nonstaff' => $trn[8],
                'kategori_program' => $trn[9],
                'fasilitator' => $trn[10],
                'nama_penyelenggara_fasilitator' => $trn[11],
                'tempat' => $trn[12],
                'online_offline' => $trn[13],
                'month' => indoMonthToNumber($trn[14]),
                'start_date' => $date_start,
                'end_date' => $date_end,
                'days' => $trn[17],
                'hours' => $trn[18],
                'total_hours' => $trn[19],
                'rmho' => $trn[20],
                'rmip' => $trn[21],
                'rebh' => $trn[22],
                'rmtu' => $trn[23],
                'rmts' => $trn[24],
                'rmgm' => $trn[25],
                'rhml' => $trn[26],
                'total_jobsite' => $trn[27],
                'total_participants' => $trn[28],
                'grand_total_hours' => $trn[29],
                'biaya_pelatihan_per_orang' => currencyStringToInteger($trn[30]),
                'biaya_pelatihan' => currencyStringToInteger($trn[31]),
                'training_kit_per_orang' => currencyStringToInteger($trn[32]),
                'training_kit' => currencyStringToInteger($trn[33]),
                'nama_hotel' => $trn[34],
                'biaya_penginapan_per_orang' => currencyStringToInteger($trn[35]),
                'biaya_penginapan' => currencyStringToInteger($trn[36]),
                'meeting_package_per_orang' => currencyStringToInteger($trn[37]),
                'meeting_package' => currencyStringToInteger($trn[38]),
                'makan_per_orang' => currencyStringToInteger($trn[39]),
                'makan' => currencyStringToInteger($trn[40]),
                'snack_per_orang' => currencyStringToInteger($trn[41]),
                'snack' => currencyStringToInteger($trn[42]),
                'tiket_per_orang' => currencyStringToInteger($trn[43]),
                'tiket' => currencyStringToInteger($trn[44]),
                'grand_total' => currencyStringToInteger($trn[45]),
                'keterangan' => $trn[46],
            ];
        }
        $data['atmps'] = $trn_data;

        return $data;
    }

    function check_file($hash)
    {
        $atmp = $this->db->get_where('trn_atmp_docs', ['md5(id)' => $hash])->row_array();
        if (!$atmp) {
            $data['status'] = "error";
            $data['message'] = "record not found";
            return $data;
        }

        $file_path = FCPATH . 'uploads/ATMP/' . $atmp['id'] . "." . pathinfo($atmp['file_name'], PATHINFO_EXTENSION);
        if (!file_exists($file_path)) {
            $data['status'] = "error";
            $data['message'] = "file not found";
            return $data;
        }

        $data['status'] = "OK";
        $data['atmp'] = $atmp;
        $data['file_path'] = $file_path;
        return $data;
    }

    function download($hash)
    {
        $data = $this->check_file($hash);
        if ($data['status'] == "error") {
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => $data['message'],
            ]);
            redirect("training/ATMP");
            return;
        }

        // Read the file content
        $file_content = file_get_contents($data['file_path']);

        // Force download with the new filename
        force_download($data['atmp']['file_name'], $file_content);
    }

    function delete($hash)
    {
        $data = $this->check_file($hash);
        if ($data['status'] == "error") {
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => $data['message'],
            ]);
            redirect("training/ATMP");
            return;
        }

        unlink($data['file_path']);

        $this->db->delete('trn_atmp_docs', ['id' => $data['atmp']['id']]);

        $this->session->set_flashdata('swal', [
            'type' => 'success',
            'message' => "File deleted successfully"
        ]);

        redirect("training/ATMP");
    }

    function edit($year)
    {
        $data['year'] = $year;
        $data['trainings'] = $this->m_atmp->get_training($year);
        $data['content'] = "training/ATMP_edit";
        $this->load->view('templates/header_footer', $data);
    }

    function submit()
    {
        if ($this->input->post('proceed') == 'N') {
            redirect('training/ATMP?year=' . $this->input->post('year'));
        }
        $success = $this->m_atmp->submit();

        $this->session->set_flashdata('swal', [
            'type' => 'success',
            'message' => "ATMP edited succesfully"
        ]);
        redirect('training/ATMP/edit/' . $this->input->post('year'));
    }

    function participant($action = 'list')
    {
        $this->load->model('training/m_trn');
        $this->load->model('training/m_trn_user');
        switch ($action) {
            case 'list':
                $hash_trn_id = $this->input->get('training_id');
                $training = $this->m_trn->get_trn($hash_trn_id, "md5(id)", false);
                $data['training'] = $training;
                $data['trn_users'] = $this->m_trn_user->get_trn_user($training['id'], "trn_id");
                // $data['users'] = $this->m_trn_user->get_user_not_trn($training['id']);
                $data['users'] = $this->db->get("rml_sso_la.users")->result_array();
                $data['content'] = "training/users";
                $this->load->view('templates/header_footer', $data);
                break;
            case 'add':
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => 'User Add Failed',
                ]);
                $hash_trn_id = $this->input->get('training_id');
                $training = $this->m_trn->get_trn($hash_trn_id, "md5(id)", false);
                $success = $this->m_trn_user->add($training['id']);
                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "User Added Success"
                    ]);
                }
                redirect("training/ATMP/participant/list?training_id=" . $hash_trn_id);
        }
    }
}
