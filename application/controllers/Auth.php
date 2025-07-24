<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function index()
    {
        $this->load->model('m_menu');
        $group = $this->m_menu->get_menu($this->session->userdata('NRP'));
        if ($group) {
            $menu = $group[0]['menus'][0];
            $url = $menu['url'];
            if ($menu['children']) {
                $url = $menu['children'][0]['url'];
            }
            redirect($url);
        }
    }

    // perbaiki
    public function logout()
    {
        $token = $this->session->userdata('token');
        $this->session->sess_destroy();
        // redirect('http://192.168.200.102/sso/auth/logout/' . $token . '?redirect_uri=' . base_url());
        redirect('http://localhost/sso/auth/logout/' . $token . '?redirect_uri=' . base_url());
        // redirect('https://sso-la.riungmitra.com/auth/logout/' . $token . '?redirect_uri=' . base_url());
    }
}
