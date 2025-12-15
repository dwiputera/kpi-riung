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
}
