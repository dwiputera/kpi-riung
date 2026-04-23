<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_user', 'm_user');
    }

    public function index()
    {
        $data['users'] = $this->m_user->get_simple();
        $data['content'] = 'user/list';
        $this->load->view('templates/header_footer', $data);
    }

    public function list_advanced()
    {
        $data['users'] = $this->m_user->get_all();
        $data['content'] = 'user/list_advanced';
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        $payload = json_decode($this->input->post('json_data'), true);

        if (!$payload) {
            flash_swal('error', 'Invalid payload');
            redirect('user');
        }

        $mode = $this->input->post('mode');

        $success = $this->m_user->submit($payload);

        flash_swal(
            $success ? 'success' : 'error',
            $success ? 'User data updated successfully' : 'Failed to update user data'
        );

        if ($mode === 'advanced') {
            redirect('user/list');
        }

        redirect('user');
    }

    public function upload_excel()
    {
        if (empty($_FILES['excel_file']['name'])) {
            flash_swal('error', 'File excel belum dipilih');
            redirect('user');
        }

        $config['upload_path']   = FCPATH . 'uploads/temp/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size']      = 20480;
        $config['encrypt_name']  = true;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            flash_swal('error', strip_tags($this->upload->display_errors()));
            redirect('user');
        }

        $uploadData = $this->upload->data();
        $filePath = $uploadData['full_path'];

        $result = $this->m_user->import_excel($filePath);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        if ($result['success']) {
            flash_swal('success', "Import selesai. Insert: {$result['inserted']}, Update: {$result['updated']}, Skip: {$result['skipped']}");
        } else {
            flash_swal('error', $result['message']);
        }

        redirect('user');
    }
}
