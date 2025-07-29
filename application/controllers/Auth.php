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

        $sso_logout_url = $sso_url . 'auth/logout/' . $token . '?redirect_uri=' . base_url();

        // langsung echo HTML dan script hapus localStorage dan redirect
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Logging out...</title></head><body>';
        echo '<p>Logging out, please wait...</p>';
        echo '<script>
            // hapus semua localStorage yang prefix-nya excelFilters_
            Object.keys(localStorage).forEach(function(key) {
                if (key.startsWith("excelFilters_")) {
                    localStorage.removeItem(key);
                }
            });
            // redirect ke SSO logout
            window.location.href = "' . htmlspecialchars($sso_logout_url, ENT_QUOTES, 'UTF-8') . '";
        </script>';
        echo '</body></html>';
    }
}
