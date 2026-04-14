<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_rtc extends CI_Model
{
    public function build_rtc_rows($positions = [], $years = [])
    {
        if (empty($positions)) {
            return [];
        }

        $oalpIds = array_column($positions, 'id');

        $existingUsers = $this->get_existing_users_by_oalp($oalpIds);
        $rtcMap        = $this->get_rtc_map($oalpIds, $years);

        $rows = [];

        foreach ($positions as $p) {
            $oalpId = (int) $p['id'];

            $fullNames = [];
            $nrps      = [];

            if (!empty($existingUsers[$oalpId])) {
                foreach ($existingUsers[$oalpId] as $u) {
                    $nrps[] = $u['NRP'];
                    $fullNames[] = $u['FullName'] ?: '-';
                }
            }

            $row = [
                'id'           => $oalpId,
                'matrix_point' => isset($p['mp_name']) ? $p['mp_name'] : '',
                'site'         => isset($p['oa_name']) ? $p['oa_name'] : '',
                'level'        => isset($p['oal_name']) ? $p['oal_name'] : '',
                'jabatan'      => isset($p['name']) ? $p['name'] : '',
                'full_name'    => !empty($fullNames) ? implode("<br>", $fullNames) : '-',
                'nrp'          => !empty($nrps) ? implode("<br>", $nrps) : '-',
                'years'        => [],
            ];

            foreach ($years as $year) {
                $assigned = isset($rtcMap[$oalpId][$year]) ? $rtcMap[$oalpId][$year] : [];
                $row['years'][$year] = $assigned;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function get_existing_users_by_oalp($oalpIds = [])
    {
        if (empty($oalpIds)) {
            return [];
        }

        $this->db->select("
            oapu.area_lvl_pstn_id,
            oapu.NRP,
            COALESCE(u.FullName, '') AS FullName
        ");
        $this->db->from('org_area_lvl_pstn_user oapu');
        $this->db->join('rml_sso_la.users u', 'u.NRP = oapu.NRP', 'left');
        $this->db->where_in('oapu.area_lvl_pstn_id', $oalpIds);
        $this->db->order_by('oapu.area_lvl_pstn_id', 'ASC');
        $this->db->order_by('oapu.NRP', 'ASC');

        $query = $this->db->get()->result_array();

        $result = [];
        foreach ($query as $row) {
            $key = (int) $row['area_lvl_pstn_id'];
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result[$key][] = $row;
        }

        return $result;
    }

    public function get_rtc_map($oalpIds = [], $years = [])
    {
        if (empty($oalpIds) || empty($years)) {
            return [];
        }

        $this->db->select("
            rtc.oalp_id,
            rtc.year,
            rtc.NRP,
            rtc.`order`,
            COALESCE(u.FullName, '') AS FullName
        ");
        $this->db->from('rtc');
        $this->db->join('rml_sso_la.users u', 'u.NRP = rtc.NRP', 'left');
        $this->db->where_in('rtc.oalp_id', $oalpIds);
        $this->db->where_in('rtc.year', $years);
        $this->db->order_by('rtc.oalp_id', 'ASC');
        $this->db->order_by('rtc.year', 'ASC');
        $this->db->order_by('rtc.`order`', 'ASC');

        $query = $this->db->get()->result_array();

        $map = [];
        foreach ($query as $row) {
            $oalpId = (int) $row['oalp_id'];
            $year   = (int) $row['year'];

            if (!isset($map[$oalpId])) {
                $map[$oalpId] = [];
            }
            if (!isset($map[$oalpId][$year])) {
                $map[$oalpId][$year] = [];
            }

            $map[$oalpId][$year][] = [
                'NRP'      => $row['NRP'],
                'FullName' => $row['FullName'],
                'order'    => (int) $row['order'],
            ];
        }

        return $map;
    }

    public function get_candidate_options($keyword = '')
    {
        $this->db->select('NRP, FullName');
        $this->db->from('rml_sso_la.users');

        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like('NRP', $keyword);
            $this->db->or_like('FullName', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('FullName', 'ASC');
        $this->db->limit(30);

        return $this->db->get()->result_array();
    }

    public function get_assignment_with_name($oalp_id, $year)
    {
        $this->db->select("
            rtc.NRP,
            rtc.`order`,
            COALESCE(u.FullName, '') AS FullName
        ");
        $this->db->from('rtc');
        $this->db->join('rml_sso_la.users u', 'u.NRP = rtc.NRP', 'left');
        $this->db->where('rtc.oalp_id', (int) $oalp_id);
        $this->db->where('rtc.year', (int) $year);
        $this->db->order_by('rtc.`order`', 'ASC');

        return $this->db->get()->result_array();
    }

    public function replace_assignment($oalp_id, $year, $nrps = [])
    {
        $oalp_id = (int) $oalp_id;
        $year    = (int) $year;

        $this->db->where('oalp_id', $oalp_id);
        $this->db->where('year', $year);
        $this->db->delete('rtc');

        if (empty($nrps)) {
            return true;
        }

        $insert = [];
        $order = 1;
        foreach ($nrps as $nrp) {
            $insert[] = [
                'oalp_id' => $oalp_id,
                'year'    => $year,
                'NRP'     => $nrp,
                'order'   => $order,
            ];
            $order++;
        }

        if (!empty($insert)) {
            $this->db->insert_batch('rtc', $insert);
        }

        return true;
    }
}
