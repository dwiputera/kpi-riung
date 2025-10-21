<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenRefreshHook
{
    private $refresh_threshold = 300; // 5 minutes
    private $sso_server;

    public function __construct()
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        if ($host === 'localhost') {
            $this->sso_server = 'http://localhost/sso/';
        } elseif (strpos($host, '192.168.') === 0 || $host === '192.168.200.102') {
            $this->sso_server = 'http://192.168.200.102/sso/';
        } else {
            $this->sso_server = 'https://sso-la.riungmitra.com/';
        }
    }

    public function check_and_refresh_token()
    {
        $CI = &get_instance();
        if (!isset($CI->session)) {
            $CI->load->library('session');
        }

        // --- WHITELIST ROUTE: Jangan ganggu Auth controller ---
        // Supaya /kpi/ (Auth::index) bisa menerima token POST dari SSO tanpa di-redirect
        $controller = strtolower($CI->router->class ?? '');
        if ($controller === 'auth' && in_array($CI->uri->segment(2), array('login', 'logout'))) {
            return; // biarkan Auth menangani login/logout sendiri
        }

        // --- Ambil token dari POST/GET/Header bila ada (prioritas POST) ---
        $incoming_token = $CI->input->post('token', true);
        if (!$incoming_token) {
            $incoming_token = $CI->input->get('token', true);
        }
        if (!$incoming_token) {
            $auth = $CI->input->server('HTTP_AUTHORIZATION') ?: $CI->input->server('Authorization');
            if ($auth && stripos($auth, 'Bearer ') === 0) {
                $incoming_token = trim(substr($auth, 7));
            }
        }

        // Jika ada token di request: simpan ke session & isi identitas, lalu JANGAN redirect di sini.
        if ($incoming_token) {
            $CI->session->set_userdata('token', $incoming_token);

            // Decode payload ringan hanya untuk ambil identitas
            try {
                JWT::$leeway = 60;
                $publicKey = @file_get_contents(APPPATH . 'keys/public.key');
                if ($publicKey) {
                    $decoded = JWT::decode($incoming_token, new Key($publicKey, 'RS256'));
                    if (!empty($decoded->data->NRP)) {
                        $CI->session->set_userdata('NRP', $decoded->data->NRP);
                        $CI->session->set_userdata('full_name', $decoded->data->full_name ?? null);
                    }
                }
            } catch (\Throwable $e) {
                // biarkan controller Auth yang menangani jika perlu
            }

            return; // penting: jangan redirect, biarkan controller tujuan berjalan
        }

        // --- Tidak ada token di request, cek token di session ---
        $token = $CI->session->userdata('token');
        if (!$token) {
            // Belum login → arahkan ke SSO login
            redirect($this->sso_login_url(base_url()));
            exit;
        }

        // --- Validasi & auto-refresh token di session ---
        $publicKey = @file_get_contents(APPPATH . 'keys/public.key');
        if (!$publicKey) {
            // Jika kunci tidak ada, paksa login ulang
            $CI->session->unset_userdata('token');
            redirect($this->sso_login_url(current_url()));
            exit;
        }

        try {
            JWT::$leeway = 60;
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            // Validasi issuer: prefix check (lebih fleksibel)
            $iss = (string)($decoded->iss ?? '');
            if (stripos($iss, rtrim($this->sso_server, '/')) !== 0) {
                throw new Exception('Invalid issuer');
            }

            $now       = time();
            $time_left = ($decoded->exp ?? 0) - $now;
            if ($time_left < 0) {
                throw new Exception('Token expired');
            }

            // Refresh jika hampir habis
            if ($time_left < $this->refresh_threshold) {
                $new_token = $this->refresh_token($token);
                if ($new_token) {
                    $CI->session->set_userdata('token', $new_token);

                    // Perbarui identitas dari token baru (opsional)
                    try {
                        $decoded2 = JWT::decode($new_token, new Key($publicKey, 'RS256'));
                        if (!empty($decoded2->data->NRP)) {
                            $CI->session->set_userdata('NRP', $decoded2->data->NRP);
                            $CI->session->set_userdata('full_name', $decoded2->data->full_name ?? null);
                        }
                    } catch (\Throwable $e) { /* abaikan */
                    }
                } else {
                    // Gagal refresh → logout session & minta login ulang
                    $CI->session->unset_userdata('token');
                    redirect($this->sso_login_url(current_url()));
                    exit;
                }
            }
        } catch (\Throwable $e) {
            // Token invalid/expired → paksa login ulang
            $CI->session->unset_userdata('token');
            redirect($this->sso_login_url(current_url()));
            exit;
        }
    }

    private function refresh_token($old_token)
    {
        if (empty($old_token)) return false;

        $url = rtrim($this->sso_server, '/') . '/auth/refresh_token';
        $max_attempts = 3; // jumlah percobaan maksimal
        $attempt = 0;

        do {
            $attempt++;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => http_build_query(['token' => $old_token]), // kirim di body
                CURLOPT_HTTPHEADER      => [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_MAXREDIRS       => 3,
                CURLOPT_CONNECTTIMEOUT  => 50,
                CURLOPT_TIMEOUT         => 60,
            ]);

            $response  = curl_exec($ch);
            $httpcode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Kalau berhasil (200), langsung return
            if ($httpcode === 200) {
                $result = json_decode($response, true);
                if (isset($result['token']) && !empty($result['token'])) {
                    return $result['token'];
                }
            }

            // Log error biar tahu kenapa gagal
            log_message('error', sprintf(
                'Refresh token attempt #%d gagal (HTTP %s): %s %s',
                $attempt,
                $httpcode ?: 'no_code',
                $curlError ? "cURL error: $curlError" : '',
                $response ?: ''
            ));

            // Delay kecil sebelum ulangi (misal 2 detik)
            sleep(2);
        } while ($attempt < $max_attempts);

        // Kalau tetap gagal setelah 3x percobaan
        return false;
    }

    private function sso_login_url($redirect_uri)
    {
        // arahkan ke halaman login SSO (auth/login) + redirect_uri
        return $this->sso_server . 'auth/login?redirect_uri=' . urlencode($redirect_uri);
    }
}
