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

    public function logout()
    {
        $token = $this->session->userdata('token');
        $this->session->sess_destroy();

        $host = $_SERVER['HTTP_HOST'];

        if ($host === 'localhost') {
            $sso_url = 'http://localhost/sso/';
        } elseif ($host === '192.168.200.102') {
            $sso_url = 'http://192.168.200.102/sso/';
        } else {
            $sso_url = 'https://sso-la.riungmitra.com/';
        }

        redirect($sso_url . 'auth/logout/' . $token . '?redirect_uri=' . base_url());
    }
}
