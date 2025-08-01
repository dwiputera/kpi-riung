<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ATMP extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('training/m_atmp');
    }

    public function index()
    {
        $year = (int) ($this->input->get('year') ?? date('Y'));
        $data = [
            'year'      => $year,
            'atmps'     => $this->m_atmp->get_atmp_docs($year),
            'trainings' => $this->m_atmp->get_atmp($year, 'trn_atmp.year'),
            'content'   => 'training/ATMP'
        ];
        $this->load->view('templates/header_footer', $data);
    }

    public function do_upload()
    {
        $this->form_validation->set_rules('year', 'Year', 'required|numeric|exact_length[4]|greater_than_equal_to[1900]|less_than_equal_to[2100]');
        if (!$this->form_validation->run()) return redirect('training/atmp');

        $config = [
            'upload_path'   => './uploads/temp/',
            'allowed_types' => 'xls|xlsx|csv',
            'max_size'      => 2048
        ];
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            $this->set_swal('error', strip_tags($this->upload->display_errors()));
            return redirect('training/atmp?year=' . $this->input->post('year'));
        }

        $upload = $this->upload->data();
        $year   = $this->input->post('year');
        $old    = $this->db->where('year', $year)->get('trn_atmp_docs')->row_array();

        // Insert record and rename file
        $this->db->insert('trn_atmp_docs', ['file_name' => $upload['client_name'], 'year' => $year]);
        $insert_id = $this->db->insert_id();
        $new_file  = './uploads/ATMP/' . $insert_id . $upload['file_ext'];
        rename($upload['full_path'], $new_file);

        $excel_data = $this->array_from_excel($new_file);
        if ($excel_data['status'] === 'error') {
            $this->cleanup_failed_upload($insert_id, $new_file, $excel_data['message']);
            return redirect('training/atmp');
        }

        if ($old) $this->remove_old_docs($old);
        $this->db->insert_batch('trn_atmp', $excel_data['atmps']);

        $this->set_swal('success', 'File uploaded successfully');
        redirect("training/ATMP?year=$year");
    }

    private function cleanup_failed_upload($id, $file, $message)
    {
        $this->set_swal('error', $message);
        @unlink($file);
        $this->db->delete('trn_atmp_docs', ['id' => $id]);
    }

    public function check_file($hash)
    {
        // Fetch ATMP document record by MD5 hash
        $atmp = $this->db->select('id, file_name, year')
            ->from('trn_atmp_docs')
            ->where('MD5(id)', $hash)
            ->get()
            ->row_array();

        if (!$atmp) {
            return [
                'status'  => 'error',
                'message' => 'Record not found'
            ];
        }

        // Construct file path and verify existence
        $extension = pathinfo($atmp['file_name'], PATHINFO_EXTENSION);
        $file_path = FCPATH . "uploads/ATMP/{$atmp['id']}.$extension";

        if (!is_file($file_path)) {
            return [
                'status'  => 'error',
                'message' => 'File not found'
            ];
        }

        return [
            'status'    => 'OK',
            'atmp'      => $atmp,
            'file_path' => $file_path
        ];
    }

    public function download($hash)
    {
        // Validate and fetch file data
        $file_data = $this->check_file($hash);

        if ($file_data['status'] === 'error') {
            $this->set_swal('error', $file_data['message']);
            return redirect('training/ATMP');
        }

        // Safely read and download file
        $this->load->helper('download');
        $file_content = @file_get_contents($file_data['file_path']);

        if ($file_content === false) {
            $this->set_swal('error', 'Unable to read file for download.');
            return redirect('training/ATMP');
        }

        force_download($file_data['atmp']['file_name'], $file_content);
    }

    public function delete($hash)
    {
        // Validate and fetch file data
        $file_data = $this->check_file($hash);

        if ($file_data['status'] === 'error') {
            $this->set_swal('error', $file_data['message']);
            return redirect('training/ATMP');
        }

        // Attempt to delete file safely
        if (is_file($file_data['file_path']) && !@unlink($file_data['file_path'])) {
            $this->set_swal('error', 'Failed to delete file from server.');
            return redirect('training/ATMP');
        }

        // Remove DB record
        $this->db->delete('trn_atmp_docs', ['id' => $file_data['atmp']['id']]);

        $this->set_swal('success', 'File deleted successfully.');
        return redirect('training/ATMP');
    }

    private function remove_old_docs($old)
    {
        $this->db->delete('trn_atmp_docs', ['id' => $old['id']]);
        $this->db->delete('trn_atmp', ['year' => $old['year']]);

        $check = $this->check_file(md5($old['id']));
        if ($check['status'] === "OK") @unlink($old["file_path"]);
    }

    public function reformat_date($input)
    {
        $input = trim($input);
        if (is_numeric($input)) {
            return (new DateTime('1899-12-30'))->modify("+{$input} days")->format('Y-m-d');
        }

        $date = DateTime::createFromFormat('Y-m-d', $input);
        if ($date && $date->format('Y-m-d') === $input) return $input;

        try {
            return (new DateTime($input))->format('Y-m-d');
        } catch (Exception) {
            return null;
        }
    }

    public function array_from_excel($file_path)
    {
        $this->load->helper(['conversion', 'extract_spreadsheet']);
        $rows = extract_spreadsheet($file_path)[0] ?? [];
        $filtered = array_filter($rows, fn($v, $k) => $k >= 8 && !empty($v[2]), ARRAY_FILTER_USE_BOTH);

        $data = ['status' => 'OK', 'atmps' => []];
        foreach ($filtered as $row => $trn) {
            $start = $trn[15] ? $this->reformat_date($trn[15]) : null;
            $end   = $trn[16] ? $this->reformat_date($trn[16]) : null;

            if (in_array('1970-01-01', [$start, $end], true)) {
                $col = numberToExcelColumn(($start === '1970-01-01') ? 5 : 6);
                return ['status' => 'error', 'message' => "error in cell {$col}" . ($row + 1)];
            }

            $data['atmps'][] = $this->map_excel_row($trn, $start, $end);
        }
        return $data;
    }

    private function map_excel_row($trn, $start, $end)
    {
        $this->load->helper('conversion');
        return [
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
            'start_date' => $start,
            'end_date' => $end,
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

    private function set_swal($type, $msg)
    {
        $this->session->set_flashdata('swal', ['type' => $type, 'message' => $msg]);
    }

    public function edit($year = null)
    {
        $year = (int) ($year ?? date('Y'));

        // Validate year before proceeding
        if ($year < 1900 || $year > 2100) {
            $this->set_swal('error', 'Invalid year provided.');
            return redirect('training/ATMP');
        }

        $data = [
            'year'      => $year,
            'trainings' => $this->m_atmp->get_atmp($year, 'trn_atmp.year'),
            'content'   => 'training/ATMP_edit'
        ];

        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $year = (int) $this->input->post('year');

        if ($year < 1900 || $year > 2100) {
            $this->set_swal('error', 'Invalid year provided.');
            return redirect('training/ATMP');
        }

        if ($this->input->post('proceed') === 'N') {
            return redirect('training/ATMP?year=' . $year);
        }

        $payload = json_decode($this->input->post('json_data'), true);
        $success = $this->m_atmp->submit($payload, $year);

        $this->set_swal($success ? 'success' : 'error', $success ? 'ATMP saved successfully' : 'No changes or failed.');
        return redirect('training/ATMP/edit/' . $year);
    }
}
