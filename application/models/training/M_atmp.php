<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_atmp extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get ATMP documents by year
     */
    public function get_atmp_docs($year)
    {
        return $this->db->select('*')
            ->from('trn_atmp_docs')
            ->where('year', (int)$year)
            ->order_by('uploaded_at', 'DESC')
            ->get()
            ->result_array();
    }

    /**
     * Get ATMP records with participant count
     */
    public function get_atmp($value = null, $by = 'md5(trn_atmp.id)', $many = true)
    {
        if ($value) {
            $this->db->where($by, "'$value'", false);
        }

        // Ambil data utama (ATMP + total participant)
        $query = $this->db->select('
            trn_atmp.*, 
            IFNULL(u.total_participant, 0) AS total_participant
        ')
            ->from('trn_atmp')
            ->join(
                '(SELECT atmp_id, COUNT(atmp_id) AS total_participant 
              FROM trn_atmp_user 
              GROUP BY atmp_id) u',
                'u.atmp_id = trn_atmp.id',
                'left'
            )
            ->get();

        $result = $many ? $query->result_array() : [$query->row_array()];

        // Ambil semua mts dalam 1 query saja
        $atmp_ids = array_column($result, 'id');

        $mts_data = [];
        if (!empty($atmp_ids)) {
            $mts_query = $this->m_mts->get_mts();

            // Grupkan mts berdasarkan atmp_id
            foreach ($mts_query as $mts) {
                $mts_data[$mts['atmp_id']][] = $mts;
            }
        }

        // Gabungkan mts ke hasil utama
        foreach ($result as &$row) {
            $row['mts'] = isset($mts_data[$row['id']]) ? $mts_data[$row['id']] : [];
        }

        return $many ? $result : $result[0];
    }

    /**
     * Submit edited ATMP data
     */
    public function submit($payload, $year)
    {
        $updates = $payload['updates'] ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $payload['creates'] ?? [];
        $success = false;

        // UPDATES
        if (!empty($updates)) {
            $ids = array_column($this->db->select('id')->get('trn_atmp')->result_array(), 'id');
            $updateData = [];
            foreach ($updates as $row) {
                if (isset($row['id']) && is_numeric($row['id']) && in_array($row['id'], $ids)) {
                    $updateData[] = $row;
                }
            }
            if (!empty($updateData)) {
                $this->db->update_batch('trn_atmp', $updateData, 'id');
                $success = true;
            }
        }

        // DELETES
        if (!empty($deletes)) {
            $this->db->where_in('id', $deletes)->delete('trn_atmp');
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
                $this->db->insert_batch('trn_atmp', $createData);
                $success = true;
            }
        }


        return $success;
    }
}
