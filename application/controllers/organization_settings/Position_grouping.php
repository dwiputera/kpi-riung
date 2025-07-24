<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Position extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('organization/m_area', 'm_area');
        // $this->load->model('organization/m_level', 'm_lvl');
        // $this->load->model('organization/m_position', 'm_pstn');
    }

    public function index()
    {
        $data['users'] = $this->m_pstn->get_users();
        $data['area'] = $this->m_area->get_area();
        $data['area_lvl'] = $this->m_lvl->get_area_lvl();
        $data['area_lvl_pstn'] = $this->get_area_lvl_pstn();
        $data['pstn_active_ids'] = [];
        if ($this->input->get('pstn_active')) {
            $pstn_active = $this->m_pstn->get_superiors($this->input->get('pstn_active'));
            $data['pstn_active_ids'] = array_column($pstn_active, 'id');
        }
        $data['area_lvl'] = $this->m_lvl->get_area_lvl();
        $data['content'] = "organization/position";
        $this->load->view('templates/header_footer', $data);
    }
}
