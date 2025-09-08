<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

defined('BASEPATH') or exit('No direct script access allowed');

class M_culture_fit extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT *, cf.id id, cf.NRP NRP, oa.name oa_name, oalp.name oalp_name FROM culture_fit cf
            LEFT JOIN rml_sso_la.users ON users.NRP = cf.NRP
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = cf.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_lvl_id
            $where
            ORDER BY cf.id
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function submit($payload, $year)
    {
        $updates = $payload['updates'] ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $payload['creates'] ?? [];

        $success = false;

        // 1. Handle UPDATES (existing rows)
        if (!empty($updates)) {
            $ids = array_column($this->db->select('id')->get('culture_fit')->result_array(), 'id');

            $updateData = [];
            foreach ($updates as $row) {
                if (isset($row['id']) && is_numeric($row['id']) && in_array($row['id'], $ids)) {
                    $updateData[] = $row;
                }
            }

            if (!empty($updateData)) {
                $this->db->update_batch('culture_fit', $updateData, 'id');
                $success = true;
            }
        }

        // 2. Handle DELETES
        if (!empty($deletes)) {
            $this->db->where_in('id', $deletes)->delete('culture_fit');
            $success = true;
        }

        // 3. Handle CREATES (new rows)
        if (!empty($creates)) {
            // Remove any rows marked as deleted
            $creates = array_filter($creates, function ($row) use ($deletes) {
                return !(isset($row['id']) && in_array($row['id'], $deletes));
            });

            $createData = [];
            foreach ($creates as $row) {
                if (isset($row['id']) && strpos($row['id'], 'new_') === 0) {
                    unset($row['id']);
                    $row['year'] = $year;
                    $createData[] = $row;
                }
            }

            if (!empty($createData)) {
                $this->db->insert_batch('culture_fit', $createData);
                $success = true;
            }
        }

        return $success;
    }
}
