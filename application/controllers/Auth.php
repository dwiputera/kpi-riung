<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends CI_Controller
{
    /* ======================== UTIL ======================== */

    private function sso_base_url()
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if ($host === 'localhost') {
            return 'http://localhost/sso/';
        } elseif ($host === '192.168.200.102') {
            return 'http://192.168.200.102/sso/';
        }
        return 'https://sso-la.riungmitra.com/'; // production fallback
    }

    private function decode_jwt_from_request()
    {
        // 1) Prioritas: POST (auto-POST dari SSO)
        $token = $this->input->post('token', true);

        // 2) Fallback: GET (dipakai kalau SSO dev mengirim via query)
        if (!$token) {
            $token = $this->input->get('token', true);
        }

        // 3) Fallback: Authorization: Bearer <token>
        if (!$token) {
            $auth = $this->input->server('HTTP_AUTHORIZATION')
                ?: $this->input->server('Authorization');
            if ($auth && stripos($auth, 'Bearer ') === 0) {
                $token = trim(substr($auth, 7));
            }
        }

        if (!$token) return [null, 'No token'];

        try {
            // Toleransi beda waktu server
            JWT::$leeway = 60;

            // Baca public key SSO
            $publicKey = file_get_contents(APPPATH . 'keys/public.key');
            if (!$publicKey) {
                return [null, 'Public key not found'];
            }

            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            // Validasi issuer (samakan dengan site_url() SSO kamu)
            $expected_iss = rtrim($this->sso_base_url(), '/') . '/'; // contoh: http://localhost/sso/
            // Karena di SSO pakai site_url(), biasanya trailing slash ada.
            // Untuk aman, cukup cek prefix host SSO:
            $iss_ok = stripos($decoded->iss ?? '', rtrim($this->sso_base_url(), '/')) === 0;
            if (!$iss_ok) {
                return [null, 'Invalid issuer'];
            }

            // (Opsional) Validasi audience = URL KPI
            // if (strpos($decoded->aud ?? '', base_url()) !== 0) {
            //     return [null, 'Invalid audience'];
            // }

            return [[$decoded, $token], null];
        } catch (\Throwable $e) {
            return [null, 'Invalid or expired token'];
        }
    }

    private function set_session_from_jwt($decoded, $token)
    {
        $nrp       = $decoded->data->NRP       ?? null;
        $full_name = $decoded->data->full_name ?? null;

        if (!$nrp) return false;

        // Anti session fixation
        $this->session->sess_regenerate(TRUE);

        $this->session->set_userdata([
            'NRP'       => $nrp,
            'full_name' => $full_name,
            'token'     => $token,
            'is_login'  => true,
        ]);
        return true;
    }

    private function goto_first_menu_or_home()
    {
        $this->load->model('m_menu');
        $group = $this->m_menu->get_menu($this->session->userdata('NRP'));

        // Jika ada menu → redirect menu pertama (atau child pertama)
        if ($group && isset($group[0]['menus'][0])) {
            $menu = $group[0]['menus'][0];
            $url  = $menu['url'] ?? base_url();
            if (!empty($menu['children'])) {
                $url = $menu['children'][0]['url'] ?? $url;
            }
            redirect($url);
            return;
        }

        // Fallback: ke home
        redirect(base_url());
    }

    private function redirect_to_sso_login()
    {
        $sso = $this->sso_base_url();
        $redirect_uri = base_url(); // KPI url sebagai target kembali
        redirect($sso . 'auth/login?redirect_uri=' . urlencode($redirect_uri));
    }

    /* ======================== ROUTES ====================== */

    // Landing KPI: jika sudah login → ke menu, jika ada token POST/GET → proses, kalau tidak → ke SSO login
    public function index()
    {
        // Sudah login? langsung ke menu
        if ($this->session->userdata('is_login')) {
            $this->goto_first_menu_or_home();
            return;
        }

        // Cek apakah ada token masuk dari SSO (POST/GET/Header)
        list($result, $err) = $this->decode_jwt_from_request();
        if ($result) {
            list($decoded, $token) = $result;

            if ($this->set_session_from_jwt($decoded, $token)) {
                $this->goto_first_menu_or_home();
                return;
            }

            // Gagal set session
            show_error('Failed to set session', 500);
            return;
        }

        // Tidak ada session dan tidak ada token → minta login ke SSO
        $this->redirect_to_sso_login();
    }

    // Endpoint ping token (opsional) – sesuai versi kamu
    public function check_token()
    {
        $token = $this->session->userdata('token');
        if (!$token) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'No token']));
            return;
        }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'status'    => 'ok',
                'token'     => $token,
                'NRP'       => $this->session->userdata('NRP'),
                'full_name' => $this->session->userdata('full_name'),
            ]));
    }

    // Logout KPI → kirim POST ke SSO logout sambil bawa token (bukan token di URL)
    public function logout()
    {
        $token = $this->session->userdata('token');
        $this->session->sess_destroy();

        $sso_url = $this->sso_base_url();
        $redirect_back = base_url();

        // Kirim form POST (token + redirect_uri) ke SSO /auth/logout
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Logging out...</title></head><body>';
        echo '<p>Logging out, please wait...</p>';
        echo '<form id="ssoLogout" method="POST" action="' . htmlspecialchars($sso_url . 'auth/logout', ENT_QUOTES, 'UTF-8') . '">';
        echo '<input type="hidden" name="token" value="' . htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') . '">';
        echo '<input type="hidden" name="redirect_uri" value="' . htmlspecialchars($redirect_back, ENT_QUOTES, 'UTF-8') . '">';
        echo '</form>';
        echo '<script>
            // bersihkan localStorage prefiks excelFilters_
            try {
              Object.keys(localStorage).forEach(function(key){
                if (key.startsWith("excelFilters_")) localStorage.removeItem(key);
              });
            } catch (e) {}
            document.getElementById("ssoLogout").submit();
        </script>';
        echo '</body></html>';
    }
}
