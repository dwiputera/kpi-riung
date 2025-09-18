<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\NumberFormat;

defined('BASEPATH') or exit('No direct script access allowed');

class M_mts extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_mts($value = null, $by = 'md5(trn_mts.id)', $many = true)
    {
        if ($value) {
            $this->db->where($by, "'$value'", false);
        }

        $query = $this->db->select('trn_mts.*, IFNULL(u.total_participant, 0) as total_participant, a.nama_program atmp_nama_program')
            ->from('trn_mts')
            ->join('(SELECT mts_id, COUNT(mts_id) AS total_participant FROM trn_mts_user GROUP BY mts_id) u', 'u.mts_id = trn_mts.id', 'left')
            ->join('trn_atmp a', 'a.id = trn_mts.atmp_id', 'left')
            ->get();

        return ($value && !$many) ? $query->row_array() : $query->result_array();
    }

    public function get_mts_atmp_chart($year)
    {
        $atmp = $this->db->get_where('trn_atmp', ['year' => $year])->result_array();
        $mts  = $this->db->get_where('trn_mts', ['year' => $year])->result_array();

        $mts_atmp = array_filter($mts, fn($m) => !empty($m['atmp_id']));
        $total    = count($atmp) + count($mts) - count($mts_atmp);

        $calc = fn($v) => $total ? number_format(($v / $total) * 100, 2) : 0;

        return [
            'total'     => $total,
            'mts'       => ['value' => count($mts) - count($mts_atmp),      'percentage' => $calc(count($mts) - count($mts_atmp))],
            'atmp'      => ['value' => count($atmp) - count($mts_atmp),     'percentage' => $calc(count($atmp) - count($mts_atmp))],
            'mts_atmp'  => ['value' => count($mts_atmp),                    'percentage' => $calc(count($mts_atmp))]
        ];
    }

    public function get_mts_status_chart($year)
    {
        $mts  = $this->db->get_where('trn_mts', ['year' => $year])->result_array();

        $total    = count($mts);
        $mts_y = array_filter($mts, fn($m) => $m['status'] == 'Y') ?? [];
        $mts_n = array_filter($mts, fn($m) => $m['status'] == 'N') ?? [];
        $mts_r = array_filter($mts, fn($m) => $m['status'] == 'R') ?? [];
        $mts_p = array_filter($mts, fn($m) => $m['status'] == 'P' || !$m['status']) ?? [];

        $percentage = function ($count, $total) {
            return $total > 0 ? number_format(($count / $total) * 100, 2) : '0.00';
        };

        return [
            'total' => $total,
            'mts_y' => ['value' => count($mts_y), 'percentage' => $percentage(count($mts_y), $total)],
            'mts_n' => ['value' => count($mts_n), 'percentage' => $percentage(count($mts_n), $total)],
            'mts_r' => ['value' => count($mts_r), 'percentage' => $percentage(count($mts_r), $total)],
            'mts_p' => ['value' => count($mts_p), 'percentage' => $percentage(count($mts_p), $total)],
        ];
    }

    public function submit($payload, $year)
    {
        $updates = $payload['updates'] ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $payload['creates'] ?? [];

        $success = false;

        // 1. Handle UPDATES (existing rows)
        if (!empty($updates)) {
            $ids = array_column($this->db->select('id')->get('trn_mts')->result_array(), 'id');

            $updateData = [];
            foreach ($updates as $row) {
                if (isset($row['id']) && is_numeric($row['id']) && in_array($row['id'], $ids)) {
                    $row['status'] = $row['status'] != '' ? $row['status'] : 'P';
                    $updateData[] = $row;
                }
            }

            if (!empty($updateData)) {
                $this->db->update_batch('trn_mts', $updateData, 'id');
                $success = true;
            }
        }

        // 2. Handle DELETES
        if (!empty($deletes)) {
            $this->db->where_in('mts_id', $deletes)->delete('trn_mts_user');
            $this->db->where_in('id', $deletes)->delete('trn_mts');
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
                    $row['status'] = $row['status'] && $row['status'] != '' ?? 'P';
                    $createData[] = $row;
                }
            }

            if (!empty($createData)) {
                $this->db->insert_batch('trn_mts', $createData);
                $success = true;
            }
        }

        return $success;
    }
}
