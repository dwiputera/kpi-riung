<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TNA extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    function index()
    {
        $this->load->model('training/m_tna');
        $data['tnas'] = $this->m_tna->get();
        $data['content'] = "training/TNA";
        $this->load->view('templates/header_footer', $data);
    }

    function do_upload()
    {
        $config['upload_path'] = './uploads/TNA/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 2048; // max size in KB

        // Step 1: Validate text input
        $this->form_validation->set_rules('year', 'Year', 'required|numeric|exact_length[4]|greater_than_equal_to[1900]|less_than_equal_to[2100]');

        if ($this->form_validation->run() == FALSE) {
            $this->index();
            return;
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => strip_tags($error)
            ]);
            $this->index();
            return;
        } else {
            $upload_data = $this->upload->data();
            $original_name = $upload_data['client_name']; // Original file name
            $file_ext = $upload_data['file_ext']; // .xls or .xlsx

            // Insert record into DB without final filename
            $this->load->database();
            $this->db->insert('trn_tna_docs', [
                'file_name' => $original_name,
                'year' => $this->input->post("year"),
            ]);
            $insert_id = $this->db->insert_id();

            // New file name using ID
            $new_filename = $insert_id . $file_ext;
            $new_path = $config['upload_path'] . $new_filename;

            // Rename the file
            rename($upload_data['full_path'], $new_path);

            flash_swal('success', 'File uploaded successfully');

            redirect("training/TNA");
            return;
        }
    }

    function check_file($hash)
    {
        $tna = $this->db->get_where('trn_tna_docs', ['md5(id)' => $hash])->row_array();
        if (!$tna) {
            $data['status'] = "error";
            $data['message'] = "record not found";
            return $data;
        }

        $file_path = FCPATH . 'uploads/TNA/' . $tna['id'] . "." . pathinfo($tna['file_name'], PATHINFO_EXTENSION);
        if (!file_exists($file_path)) {
            $data['status'] = "error";
            $data['message'] = "file not found";
            return $data;
        }

        $data['status'] = "OK";
        $data['tna'] = $tna;
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
            redirect("training/TNA");
            return;
        }

        // Read the file content
        $file_content = file_get_contents($data['file_path']);

        // Force download with the new filename
        force_download($data['tna']['file_name'], $file_content);
    }

    function delete($hash)
    {
        $data = $this->check_file($hash);
        if ($data['status'] == "error") {
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => $data['message'],
            ]);
            redirect("training/TNA");
            return;
        }

        unlink($data['file_path']);

        $this->db->delete('trn_tna_docs', ['id' => $data['tna']['id']]);

        flash_swal('success', 'File deleted successfully');

        redirect("training/TNA");
    }
}
