<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Level extends MY_Controller
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
        $data['levels'] = $this->m_level->get_area_lvl();
        $data['content'] = "organization/level";
        $this->load->view('templates/header_footer', $data);
    }

    public function add()
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Level Add Failed"
        ]);
        $success = $this->m_level->add();
        if ($success) {
            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "Level Added Successfully"
            ]);
        }
        redirect('organization_settings/level');
    }

    public function delete($hash_id)
    {
        $this->load->model('organization/m_position', 'm_pstn');
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Level Delete Failed"
        ]);
        $position = $this->m_level->get_area_lvl_pstn($hash_id);
        if (!$position) {
            $success = $this->m_level->delete($hash_id);
            if ($success) {
                $this->session->set_flashdata('swal', [
                    'type' => 'success',
                    'message' => "Level Deleted Successfully"
                ]);
            }
        } else {
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => "Please Delete the Position First"
            ]);
        }
        redirect('organization_settings/level');
    }
}
