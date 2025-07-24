<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Area extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('organization/m_area', 'm_area');
        $this->load->model('organization/m_level', 'm_level');
    }
    public function index()
    {
        $data['areas'] = $this->m_area->get_area();
        $data['content'] = "organization/area";
        $this->load->view('templates/header_footer', $data);
    }

    public function add()
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Area Add Failed"
        ]);
        $success = $this->m_area->add();
        if ($success) {
            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "Area Added Successfully"
            ]);
        }
        redirect('organization_settings/area');
    }

    public function delete($hash_id)
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Area Delete Failed"
        ]);
        $position = $this->m_area->get_area_lvl($hash_id);
        if (!$position) {
            $success = $this->m_area->delete($hash_id);
            if ($success) {
                $this->session->set_flashdata('swal', [
                    'type' => 'success',
                    'message' => "Area Deleted Successfully"
                ]);
            }
        } else {
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => "Please Delete the Level First"
            ]);
        }
        redirect('organization_settings/area');
    }
}
