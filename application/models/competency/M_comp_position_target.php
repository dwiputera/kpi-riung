<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_position_target extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_comp_position_target($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT * FROM comp_pstn_target clt
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
        $positions     = $this->m_pstn->get_area_lvl_pstn();
        $competencies  = $this->m_c_pstn->get_comp_position();
        $targets       = $this->get_comp_position_target(); // data existing di DB

        $input = json_decode($this->input->post('target_json'), true);
        if (!is_array($input)) return false;

        $data_updates = [];
        $data_inserts = [];

        foreach ($input as $i_cp_subm => $cp_subm_i) {
            $target_cp = array_filter($targets, function ($cpt_i) use ($i_cp_subm) {
                return md5($cpt_i['comp_pstn_id']) === $i_cp_subm;
            });

            foreach ($cp_subm_i as $i_pstn_subm => $pstn_subm_i) {
                if (!is_numeric($pstn_subm_i)) continue;
                $target_found = null;

                foreach ($target_cp as $cpt_i) {
                    if (md5($cpt_i['area_lvl_pstn_id']) === $i_pstn_subm) {
                        $target_found = $cpt_i;
                        break;
                    }
                }

                if ($target_found) {
                    $data_updates[] = [
                        'id'     => $target_found['id'],
                        'target' => $pstn_subm_i,
                    ];
                } else {
                    // Cari ID asli dari hash
                    $comp_pstn = array_filter($competencies, function ($c) use ($i_cp_subm) {
                        return md5($c['id']) === $i_cp_subm;
                    });
                    $comp_pstn = reset($comp_pstn);

                    $position = array_filter($positions, function ($p) use ($i_pstn_subm) {
                        return md5($p['id']) === $i_pstn_subm;
                    });
                    $position = reset($position);

                    if ($comp_pstn && $position) {
                        $data_inserts[] = [
                            'comp_pstn_id'     => $comp_pstn['id'],
                            'area_lvl_pstn_id' => $position['id'],
                            'target'           => $pstn_subm_i,
                        ];
                    }
                }
            }
        }

        $success_update = true;
        if ($data_updates) $success_update = $this->db->update_batch('comp_pstn_target', $data_updates, 'id');

        $success_insert = true;
        if ($data_inserts) $success_insert = $this->db->insert_batch('comp_pstn_target', $data_inserts);

        return $success_update && $success_insert;
    }
}
