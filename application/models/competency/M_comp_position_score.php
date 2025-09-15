<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_position_score extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_cp_score($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT * FROM comp_pstn_score clt
            LEFT JOIN comp_position cp ON cp.id = clt.comp_pstn_id
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function submit()
    {
        $nrps = json_decode($this->input->post('json_data'));
        $success = true;
        foreach ($nrps as $i_nrp => $nrp_i) {
            foreach ($nrp_i as $cp_id => $score) {
                $cps = $this->db->get_where('comp_pstn_score', array('NRP' => $i_nrp, 'comp_pstn_id' => $cp_id))->row_array();
                if ($cps) {
                    $data = ['score' => $score];
                    $success = $this->db->where('id', $cps['id'])->update('comp_pstn_score', $data);
                } else {
                    $data = [
                        'NRP' => $i_nrp,
                        'comp_pstn_id' => $cp_id,
                        'score' => $score,
                    ];
                    $success = $this->db->insert('comp_pstn_score', $data);
                }
                if (!$success) return $success;
            }
        }
        return $success;
    }
}
