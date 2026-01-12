<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_tour_of_duty extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_tod($where = '', $many = true)
    {
        $query = $this->db->query("
            SELECT * FROM emp_tour_of_duty
            $where
        ");
        if ($many == false) return $query->row_array();
        return $query->result_array();
    }

    public function get_tod_mp($where = '', $many = true)
    {
        $query = $this->db->query("
            SELECT * FROM emp_tour_of_duty_mp etodmp
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = etodmp.matrix_point_id
            $where
        ");
        if ($many == false) return $query->row_array();
        return $query->result_array();
    }

    public function get_tod_with_mp($where = '')
    {
        $tod = $this->get_tod($where);
        foreach ($tod as &$tod_i) {
            $tod_i['matrix_points'] = $this->get_tod_mp("WHERE emp_tour_of_duty_id = $tod_i[id]");
        }
        return $tod;
    }

    function calculate_duration_per_matrix_point($history)
    {
        if (empty($history)) return [];

        // 1. Sort by date ASC (paling lama dulu)
        usort($history, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $durations = [];

        $count = count($history);
        for ($i = 0; $i < $count; $i++) {
            $row = $history[$i];

            // start date periode ini
            $start = new DateTime($row['date']);

            // end date = date record berikutnya - 1 hari
            if ($i < $count - 1) {
                $next = $history[$i + 1];
                $end  = new DateTime($next['date']);
                $end->modify('-1 day');
            } else {
                // record terbaru, anggap sampai hari ini
                $end = new DateTime(date('Y-m-d'));
            }

            // selisih hari (inklusive)
            $interval = $start->diff($end);
            $days     = $interval->days + 1; // +1 kalau mau inklusif

            // 2. Akumulasi ke setiap matrix_point di periode ini
            if (!empty($row['matrix_points'])) {
                foreach ($row['matrix_points'] as $mp) {

                    $key = $mp['matrix_point_id']; // atau pakai $mp['name'] kalau mau per nama

                    if (!isset($durations[$key])) {
                        $durations[$key] = [
                            'matrix_point_id' => $mp['matrix_point_id'],
                            'name'            => $mp['name'],
                            'total_days'      => 0,
                        ];
                    }

                    $durations[$key]['total_days'] += $days;
                }
            }
        }

        // 3. Opsional: konversi hari jadi tahun-bulan-hari biar enak dibaca
        foreach ($durations as &$d) {
            $base = new DateTimeImmutable('2000-01-01');
            $plus = $base->modify('+' . $d['total_days'] . ' days');
            $int  = $base->diff($plus);

            $d['year_count'] = $int->y;
            $d['month_count'] = $int->m;
            $d['day_count'] = $int->d;
            $d['formatted'] = sprintf(
                '%d tahun %d bulan %d hari',
                $int->y,
                $int->m,
                $int->d
            );
        }

        return $durations;
    }

    function emptyStringToNull($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'emptyStringToNull'], $data);
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->emptyStringToNull($v);
            }
            return $data;
        }
        return $data === '' ? null : $data;
    }

    public function submit($payload)
    {
        $updates = $this->emptyStringToNull($payload['updates']) ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $this->emptyStringToNull($payload['creates']) ?? [];

        $success = false;

        // helper: parse "1,2,3" => [1,2,3]
        $parseMatrixPoints = function ($val) {
            if ($val === null) return [];
            if (is_array($val)) return array_values(array_unique(array_filter(array_map('intval', $val))));
            $parts = preg_split('/\s*,\s*/', trim((string)$val));
            $ints  = array_map('intval', array_filter($parts, fn($x) => $x !== ''));
            $ints  = array_values(array_unique(array_filter($ints, fn($x) => $x > 0)));
            return $ints;
        };

        // helper: sync mp rows for 1 emp_tour_of_duty_id
        $syncMp = function ($todId, array $mpIds) {
            // delete existing
            $this->db->where('emp_tour_of_duty_id', $todId)->delete('emp_tour_of_duty_mp');

            // insert new if any
            if (!empty($mpIds)) {
                $rows = [];
                foreach ($mpIds as $mpId) {
                    $rows[] = [
                        'emp_tour_of_duty_id' => (int)$todId,
                        'matrix_point_id'     => (int)$mpId,
                    ];
                }
                $this->db->insert_batch('emp_tour_of_duty_mp', $rows);
            }
        };

        $this->db->trans_start();

        /**
         * 1) DELETES (hapus child dulu baru parent)
         */
        if (!empty($deletes)) {
            $this->db->where_in('emp_tour_of_duty_id', $deletes)->delete('emp_tour_of_duty_mp');
            $this->db->where_in('id', $deletes)->delete('emp_tour_of_duty');
            $success = true;
        }

        /**
         * 2) UPDATES
         * - update kolom di emp_tour_of_duty (kecuali matrix_points)
         * - sync matrix points ke table mp
         */
        if (!empty($updates)) {
            // valid ids
            $ids = array_column(
                $this->db->select('id')->get('emp_tour_of_duty')->result_array(),
                'id'
            );

            $updateData = [];
            $mpToSync   = []; // [todId => [mpIds]]

            foreach ($updates as $row) {
                if (!isset($row['id']) || !is_numeric($row['id'])) continue;
                $id = (int)$row['id'];
                if (!in_array($id, $ids)) continue;

                // ambil & parse mp
                $mpIds = $parseMatrixPoints($row['matrix_points'] ?? null);
                $mpToSync[$id] = $mpIds;

                // buang matrix_points supaya tidak ikut update ke tabel utama
                unset($row['matrix_points']);

                $updateData[] = $row;
            }

            if (!empty($updateData)) {
                $this->db->update_batch('emp_tour_of_duty', $updateData, 'id');
                $success = true;
            }

            // sync mp per row
            foreach ($mpToSync as $todId => $mpIds) {
                $syncMp($todId, $mpIds);
                $success = true;
            }
        }

        /**
         * 3) CREATES
         * - insert per row supaya dapat insert_id()
         * - lalu insert ke emp_tour_of_duty_mp
         */
        if (!empty($creates)) {
            // remove rows marked deleted
            $creates = array_filter($creates, function ($row) use ($deletes) {
                return !(isset($row['id']) && in_array($row['id'], $deletes));
            });

            foreach ($creates as $row) {
                if (!isset($row['id']) || strpos($row['id'], 'new_') !== 0) continue;

                // parse mp, lalu buang dari row parent
                $mpIds = $parseMatrixPoints($row['matrix_points'] ?? null);
                unset($row['matrix_points']);

                unset($row['id']);

                // insert parent
                $this->db->insert('emp_tour_of_duty', $row);
                $newId = $this->db->insert_id();

                // insert child mp
                if ($newId) {
                    $syncMp($newId, $mpIds);
                    $success = true;
                }
            }
        }

        $this->db->trans_complete();

        // kalau ada error transaction, anggap gagal
        if ($this->db->trans_status() === false) {
            return false;
        }

        return $success;
    }
}
