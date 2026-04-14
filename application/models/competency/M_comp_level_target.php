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
        if ($value) {
            $where = "WHERE $by = " . $this->db->escape($value);
        }

        $query = $this->db->query("
            SELECT *
            FROM comp_lvl_target
            $where
        ");

        if (($value && !$many) || $many == false) {
            return $query->row_array();
        }

        return $query->result_array();
    }

    public function submit()
    {
        $targets = $this->input->post('targets');
        if (!is_array($targets) || empty($targets)) {
            return false;
        }

        $this->db->trans_begin();

        foreach ($targets as $area_lvl_pstn_id => $compTargets) {
            if (!is_array($compTargets)) {
                continue;
            }

            foreach ($compTargets as $comp_lvl_id => $target) {
                $area_lvl_pstn_id = (int) $area_lvl_pstn_id;
                $comp_lvl_id      = (int) $comp_lvl_id;

                // bersihkan value
                $target = trim((string)$target);
                $target = str_replace(',', '.', $target);
                $target = ($target === '') ? 0 : (float)$target;

                $existing = $this->db
                    ->get_where('comp_lvl_target', [
                        'area_lvl_pstn_id' => $area_lvl_pstn_id,
                        'comp_lvl_id'      => $comp_lvl_id,
                    ])
                    ->row_array();

                $data = [
                    'area_lvl_pstn_id' => $area_lvl_pstn_id,
                    'comp_lvl_id'      => $comp_lvl_id,
                    'target'           => $target,
                ];

                if ($existing) {
                    $this->db->where('id', $existing['id']);
                    $this->db->update('comp_lvl_target', $data);
                } else {
                    $this->db->insert('comp_lvl_target', $data);
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }
}
