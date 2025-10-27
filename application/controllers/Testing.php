<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Testing extends MY_Controller
{

    public function animation()
    {
        $data['content'] = "animation";
        $this->load->view('templates/header_footer', $data);
    }

    public function tetris()
    {
        $this->load->view('tetris');
    }

    public function chess()
    {
        $this->load->view('chess');
    }

    public function index()
    {
        // $this->load->view('testing');
        $data['content'] = 'testing';
        $this->load->view('template', $data);
    }

    public function submit()
    {
        echo '<pre>', print_r($this->input->post(), true);
        die;
    }
}
