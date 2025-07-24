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
        $host = $_SERVER['HTTP_HOST'];

        if ($host === 'localhost') {
            $this->sso_server = 'http://localhost/sso/';
        } elseif (strpos($host, '192.168.') === 0) {
            $this->sso_server = 'http://192.168.200.102/sso/';
        } else {
            $this->sso_server = 'https://sso-la.riungmitra.com/';
        }
    }

    public function check_and_refresh_token()
    {
        $CI = &get_instance();
        // Pastikan session diload
        if (!isset($CI->session)) {
            $CI->load->library('session');
        }

        // Ambil token dari query string jika ada
        $token = $CI->input->get('token');
        if ($token) {
            // Simpan token ke session
            $CI->session->set_userdata('token', $token);

            // Decode payload untuk ambil NRP
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (!empty($payload['data']['NRP'])) {
                    $CI->session->set_userdata('NRP', $payload['data']['NRP']);
                    $CI->session->set_userdata('full_name', $payload['data']['full_name']);
                }
            }

            // Redirect ke URL tanpa ?token=
            redirect(current_url());
            exit;
        }

        // Ambil token dari session
        $token = $CI->session->userdata('token');
        if (!$token) {
            redirect($this->sso_server . '?redirect_uri=' . base_url());
            exit;
        }

        $publicKey = file_get_contents(APPPATH . 'keys/public.key');

        try {
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            if ($decoded->iss !== $this->sso_server . '') {
                throw new Exception('Invalid issuer');
            }

            $now = time();
            $time_left = $decoded->exp - $now;

            if ($time_left < 0) {
                throw new Exception('Token expired');
            }

            if ($time_left < $this->refresh_threshold) {
                // Token hampir expired, refresh
                $new_token = $this->refresh_token($token);
                if ($new_token) {
                    $CI->session->set_userdata('token', $new_token);
                    // Decode payload untuk ambil NRP
                    $parts = explode('.', $new_token);
                    if (count($parts) === 3) {
                        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                        if (!empty($payload['data']['NRP'])) {
                            $CI->session->set_userdata('NRP', $payload['data']['NRP']);
                            $CI->session->set_userdata('full_name', $payload['data']['full_name']);
                        }
                    }
                } else {
                    $CI->session->unset_userdata('token');
                    redirect($this->sso_server . '?redirect_uri=' . current_url());
                    exit;
                }
            }
        } catch (Exception $e) {
            // Token tidak valid atau expired
            $CI->session->unset_userdata('token');
            redirect($this->sso_server . '?redirect_uri=' . current_url());
            exit;
        }
    }

    private function refresh_token($old_token)
    {
        $ch = curl_init($this->sso_server . 'auth/refresh_token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $old_token,
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode === 200) {
            $result = json_decode($response, true);
            if (isset($result['token'])) {
                return $result['token'];
            }
        }
        return false;
    }
}
