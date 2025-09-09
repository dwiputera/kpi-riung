<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Talent extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_talent', 'm_tal');
        $this->load->model('organization/M_position', 'm_p');
    }

    public function index()
    {
        $data['positions'] = $this->m_p->get_area_lvl_pstn([1, 2, 3, 4, 6, 7, 8, 9], 'oal.id');
        $data['content'] = "talent/position_list";
        $this->load->view('templates/header_footer', $data);
    }

    public function candidate_list($pstn_hash)
    {
        $position = $this->m_p->get_area_lvl_pstn($pstn_hash, 'md5(oalp.id)', false);
        $data['position'] = $position;
        $data['employees'] = $this->m_tal->get_candidate(md5($position['oal_id']), $position['mp_id']);
        $data['percentage'] = $this->m_tal->get_percentage($position['oal_id']);
        $data['content'] = "talent/candidate_list";
        $this->load->view('templates/header_footer', $data);
    }
}
