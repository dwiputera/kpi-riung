<?php

defined('BASEPATH') or exit('No direct script access allowed');

class M_hav_rcrd extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    function get($where = '', $many = true)
    {
        $query = $this->db->query("
            SELECT * FROM emp_hav_rcrd
            $where
        ");
        if ($many == false) return $query->row_array();
        return $query->result_array();
    }
}
