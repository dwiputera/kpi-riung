<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_user extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    function json()
    {
        $users_json = json_decode(file_get_contents(__DIR__ . '/import_user.json'), true);

        foreach ($users_json as $i_uj => $uj_i) {
            $user_db = $this->db->get_where('rml_sso_la.users', array('NRP' => $uj_i['NRP']))->row_array();
            if ($user_db) {
                // update here
                // echo '<pre>', print_r($uj_i['NRP'], true);
            } else {
                $newDate = date("dmY", strtotime($uj_i['BirthDate']));
                $uj_i['password'] = password_hash($newDate, PASSWORD_DEFAULT);
                echo '<pre>', print_r($uj_i['NRP'], true);
                $this->db->insert('rml_sso_la.users', $uj_i);
            }
        }
        die;
    }
}
