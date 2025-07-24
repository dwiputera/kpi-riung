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

        if (strpos($host, 'localhost') !== false || strpos($host, '192.168.') === 0) {
            $sso_url = 'http://localhost/sso/';
        } else {
            $sso_url = 'https://sso-la.riungmitra.com/';
        }

        $redirect_uri = base_url();
        redirect($sso_url . 'auth/logout/' . $token . '?redirect_uri=' . $redirect_uri);
    }
}
