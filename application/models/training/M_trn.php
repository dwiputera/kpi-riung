<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_trn extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_trn($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT * FROM trn
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }
}
