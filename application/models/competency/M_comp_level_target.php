<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_level_target extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_comp_level_target($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT * FROM comp_lvl_target clt
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
        $json = $this->input->post('target_json');

        if (!$json) return false;

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) return false;

        $positions = $this->m_pstn->get_area_lvl_pstn();         // area_lvl_pstn_id
        $competencies = $this->m_c_lvl->get_comp_level();        // comp_lvl_id
        $targets = $this->m_c_l_targ->get_comp_level_target();   // existing comp_lvl_target

        $data_updates = [];
        $data_inserts = [];

        foreach ($decoded as $hashed_comp_lvl_id => $position_map) {
            // Cari comp_lvl_id asli
            $comp_lvl = array_filter($competencies, function ($c) use ($hashed_comp_lvl_id) {
                return md5($c['id']) === $hashed_comp_lvl_id;
            });
            $comp_lvl = reset($comp_lvl);
            if (!$comp_lvl) continue;

            foreach ($position_map as $hashed_pos_id => $target_value) {
                if ($target_value === '' || $target_value === null) continue;

                // Cari area_lvl_pstn_id asli
                $position = array_filter($positions, function ($p) use ($hashed_pos_id) {
                    return md5($p['id']) === $hashed_pos_id;
                });
                $position = reset($position);
                if (!$position) continue;

                // Cek apakah sudah ada entry di tabel target
                $existing = array_filter($targets, function ($t) use ($comp_lvl, $position) {
                    return $t['comp_lvl_id'] == $comp_lvl['id'] && $t['area_lvl_pstn_id'] == $position['id'];
                });
                $existing = reset($existing);

                if ($existing) {
                    $data_updates[] = [
                        'id' => $existing['id'],
                        'target' => $target_value
                    ];
                } else {
                    $data_inserts[] = [
                        'comp_lvl_id' => $comp_lvl['id'],
                        'area_lvl_pstn_id' => $position['id'],
                        'target' => $target_value
                    ];
                }
            }
        }

        $success_update = true;
        $success_insert = true;

        if (!empty($data_updates)) {
            $success_update = $this->db->update_batch('comp_lvl_target', $data_updates, 'id');
        }

        if (!empty($data_inserts)) {
            $success_insert = $this->db->insert_batch('comp_lvl_target', $data_inserts);
        }

        return $success_update && $success_insert;
    }
}
