<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_position extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_comp_position($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $this->db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
        $query = $this->db->query("
            SELECT cp.*, cpd.*, cp.id id, cpd.id cpd_id, oalp.name oalp_name FROM comp_position cp
            LEFT JOIN comp_pstn_dict cpd ON cpd.comp_pstn_id = cp.id
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = cp.area_lvl_pstn_id
            $where
            GROUP BY cp.id
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function add()
    {
        $area_lvl_pstn = $this->m_pstn->get_area_lvl_pstn($this->input->post('hash_area_lvl_pstn_id'), 'md5(oalp.id)', false);

        if ($area_lvl_pstn) {
            $this->db->trans_start();

            $data = [
                'area_lvl_pstn_id' => $area_lvl_pstn['id'],
                'name' => $this->input->post('comp_pstn_name', true),
            ];
            $this->db->insert('comp_position', $data);

            $comp_pstn_id = $this->db->insert_id();

            $dict = [
                'comp_pstn_id' => $comp_pstn_id,
                'definition'   => $this->input->post('definition'),
                'level_1'      => $this->input->post('level_1'),
                'level_2'      => $this->input->post('level_2'),
                'level_3'      => $this->input->post('level_3'),
                'level_4'      => $this->input->post('level_4'),
                'level_5'      => $this->input->post('level_5'),
            ];
            $this->db->insert('comp_pstn_dict', $dict);

            $this->db->trans_complete();
            return $this->db->trans_status();
        }

        return false;
    }

    public function edit()
    {
        $hash_comp_pstn_id = $this->input->post('hash_comp_pstn_id');

        $this->db->trans_start();

        $data = [
            'name' => $this->input->post('comp_pstn_name', true),
        ];
        $this->db->where('md5(id)', $hash_comp_pstn_id);
        $this->db->update('comp_position', $data);

        $comp = $this->db->get_where('comp_position', ['md5(id)' => $hash_comp_pstn_id])->row_array();

        if ($comp) {
            $dict = [
                'definition' => $this->input->post('definition'),
                'level_1'    => $this->input->post('level_1'),
                'level_2'    => $this->input->post('level_2'),
                'level_3'    => $this->input->post('level_3'),
                'level_4'    => $this->input->post('level_4'),
                'level_5'    => $this->input->post('level_5'),
            ];

            $exist = $this->db->get_where('comp_pstn_dict', ['comp_pstn_id' => $comp['id']])->row_array();

            if ($exist) {
                $this->db->where('comp_pstn_id', $comp['id']);
                $this->db->update('comp_pstn_dict', $dict);
            } else {
                $dict['comp_pstn_id'] = $comp['id'];
                $this->db->insert('comp_pstn_dict', $dict);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($hash_id)
    {
        $this->db->where('md5(comp_pstn_id)', $hash_id);
        $success_pstn_dict = $this->db->delete('comp_pstn_dict');
        $this->db->where('md5(comp_pstn_id)', $hash_id);
        $success_pstn_target = $this->db->delete('comp_pstn_target');
        $this->db->where('md5(id)', $hash_id);
        $success_pstn = $this->db->delete('comp_position');
        if ($success_pstn_target && $success_pstn && $success_pstn_dict) return true;
        return false;
    }

    public function get_correlation_matrix()
    {
        // Ambil data
        $matrix_points = $this->db->get_where('org_area_lvl_pstn', ['type' => 'matrix_point'])->result_array();
        $comp_pstns     = $this->db->get('comp_position')->result_array();

        // Map: area_lvl_pstn_id -> [nama posisi...]
        $comp_pstns_mp = [];
        foreach ($matrix_points as $mp) {
            $list = array_filter($comp_pstns, fn($cp) => $cp['area_lvl_pstn_id'] == $mp['id']);
            $comp_pstns_mp[$mp['id']] = array_values(array_column($list, 'name')); // pastikan reindex
        }
        unset($mp, $list);

        // Bangun correlation matrix (persen overlap terhadap baris)
        $correlation_matrix = $matrix_points; // copy nilai dasar
        foreach ($correlation_matrix as &$cm) {
            $rowId = $cm['id'];
            $rowList = $comp_pstns_mp[$rowId] ?? [];
            $rowCount = count($rowList);

            // siapkan array correlations
            $cm['correlations'] = [];

            foreach ($matrix_points as $mp) { // tidak perlu by-ref
                $colId = $mp['id'];
                $colList = $comp_pstns_mp[$colId] ?? [];
                $colCount = count($colList);

                // hitung irisan
                $common = count(array_intersect($rowList, $colList));

                // if ($cm['name'] == 'HUMAN CAPITAL' && $mp['name'] == 'LEARNING ACADEMY') {
                //     echo '<pre>', print_r($rowList, true);
                //     echo '<pre>', print_r($colList, true);
                //     $cmn = array_intersect($rowList, $colList);
                //     echo '<pre>', print_r($cmn, true);
                //     $diff1 = array_diff($rowList, $cmn);
                //     echo '<pre>', print_r($diff1, true);
                //     $diff2 = array_diff($colList, $cmn);
                //     echo '<pre>', print_r($diff2, true);
                //     die;
                // }

                // hitung persen relatif thd baris (hindari /0)
                $pct = 0;
                if ($colCount > 0 && $common > 0) {
                    $pct = $common / $colCount * 100;
                }

                // simpan 2 desimal, titik sebagai decimal separator
                $cm['correlations'][$colId] = number_format($pct, 2, '.', '');
            }
        }

        return $correlation_matrix;
    }

    function dictionary_submit()
    {
        $dictionaries = json_decode($this->input->post('target_json'));
        $dictionaries = array_map(function ($v) {
            return (array) $v;           // ubah tiap inner stdClass jadi array
        }, (array) $dictionaries);                // ubah top-level stdClass jadi array

        foreach ($dictionaries as $i_dict => $dict_i) {
            $exist = $this->db->get_where('comp_pstn_dict', array('comp_pstn_id' => $i_dict))->result_array();
            if ($exist) {
                $success = $this->db->where('comp_pstn_id', $i_dict)->update('comp_pstn_dict', $dict_i);
            } else {
                $dict_i['comp_pstn_id'] = $i_dict;
                $success = $this->db->insert('comp_pstn_dict', $dict_i);
            }
        }
        return true;
    }
}
