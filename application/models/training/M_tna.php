<?php
defined('BASEPATH') or exit('No direct script access allowed');

class m_tna extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $query = $this->db->order_by("uploaded_at", "desc")->get('trn_tna_docs')->result_array();
        return $query;
    }
}
