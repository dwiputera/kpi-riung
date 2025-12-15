<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_level_score extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_cl_score($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT clas.*, cl.*, cla.*, clas.score clas_score
            FROM comp_lvl_assess_score clas
            LEFT JOIN comp_lvl_assess cla ON cla.id = clas.comp_lvl_assess_id
            JOIN (
                SELECT NRP, comp_lvl_id, MAX(tahun) AS max_tahun
                FROM comp_lvl_assess_score clas
                LEFT JOIN comp_lvl_assess cla ON cla.id = clas.comp_lvl_assess_id
                GROUP BY NRP, comp_lvl_id
            ) clas_mxyr
            ON clas_mxyr.NRP = cla.NRP
            AND clas_mxyr.comp_lvl_id = clas.comp_lvl_id
            AND clas_mxyr.max_tahun = cla.tahun
            JOIN comp_lvl cl ON cl.id = clas.comp_lvl_id
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_cl_score_latest($where, $many = true)
    {
        $query = $this->db->query("
            SELECT 
                clas.*,
                cl.*,
                cla.*,
                clas.score AS clas_score
            FROM comp_lvl_assess_score clas
            JOIN comp_lvl_assess cla 
                ON cla.id = clas.comp_lvl_assess_id
            JOIN (
                SELECT 
                    cla.NRP, 
                    clas.comp_lvl_id, 
                    MAX(cla.tahun) AS max_tahun
                FROM comp_lvl_assess_score clas
                JOIN comp_lvl_assess cla 
                    ON cla.id = clas.comp_lvl_assess_id
                $where
                GROUP BY cla.NRP, clas.comp_lvl_id
            ) clas_mxyr
                ON clas_mxyr.NRP = cla.NRP
            AND clas_mxyr.comp_lvl_id = clas.comp_lvl_id
            AND clas_mxyr.max_tahun = cla.tahun
            JOIN comp_lvl cl 
                ON cl.id = clas.comp_lvl_id
            $where
        ");
        if ($many == false) return $query->row_array();
        return $query->result_array();
    }

    public function get_cl_score_primary($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "AND $by = '$value'";
        $query = $this->db->query("
            SELECT 
                clas.*,
                cl.*,
                cla.*,
                clas.score AS clas_score
            FROM comp_lvl_assess_score clas
            JOIN comp_lvl_assess cla 
                ON cla.id = clas.comp_lvl_assess_id
            JOIN comp_lvl cl 
                ON cl.id = clas.comp_lvl_id
            WHERE 
                -- 1) Ambil TAHUN TERAKHIR per (NRP, comp_lvl_id)
                cla.tahun = (
                    SELECT 
                        MAX(cla2.tahun)
                    FROM comp_lvl_assess_score clas2
                    JOIN comp_lvl_assess cla2 
                        ON cla2.id = clas2.comp_lvl_assess_id
                    WHERE 
                        cla2.NRP = cla.NRP
                        AND clas2.comp_lvl_id = clas.comp_lvl_id
                )
                AND 
                -- 2) Di tahun terakhir itu, pilih method_id:
                --    - kalau ada yang '1', pakai '1'
                --    - kalau tidak ada, pakai MIN(method_id)
                cla.method_id = (
                    SELECT 
                        CASE 
                            WHEN SUM(CASE WHEN cla3.method_id = '1' THEN 1 ELSE 0 END) > 0 
                                THEN '1'
                            ELSE MIN(cla3.method_id)
                        END AS chosen_method_id
                    FROM comp_lvl_assess_score clas3
                    JOIN comp_lvl_assess cla3 
                        ON cla3.id = clas3.comp_lvl_assess_id
                    WHERE 
                        cla3.NRP = cla.NRP
                        AND clas3.comp_lvl_id = clas.comp_lvl_id
                        AND cla3.tahun = cla.tahun
                )
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_cl_score_year($value = null, $by = 'md5(id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT clas.*, cla.*, clas.score clas_score
            FROM comp_lvl_assess_score clas
            LEFT JOIN comp_lvl_assess cla ON cla.id = clas.comp_lvl_assess_id
            #LEFT JOIN comp_lvl cl ON cl.id = clas.comp_lvl_id
            #JOIN comp_lvl cl ON cl.id = clas.comp_lvl_id
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
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

    public function submit()
    {
        $nrps = (array) json_decode($this->input->post('json_data'));
        $nrps = $this->emptyStringToNull($nrps);
        $success = true;
        $cla_columns = ['vendor', 'recommendation', 'remarks', 'score'];
        $filled = array_filter($nrps, function ($obj) {
            return count(array_filter((array) $obj)) > 0;
        });
        $empty = array_diff_key($nrps, $filled);

        foreach ($empty as $i_nrp => $nrp_i) {
            $cla = $this->db
                ->get_where('comp_lvl_assess', array('NRP' => $i_nrp, 'method_id' => $this->input->post('method_id'), 'tahun' => $this->input->post('year')))
                ->row_array();
            if ($cla) {
                $success = $this->db->where('comp_lvl_assess_id', $cla['id'])->delete('comp_lvl_assess_score');
                $success = $this->db->where('id', $cla['id'])->delete('comp_lvl_assess');
            }
        }

        foreach ($filled as $i_nrp => $nrp_i) {
            $cla = $this->db
                ->get_where('comp_lvl_assess', array('NRP' => $i_nrp, 'method_id' => $this->input->post('method_id'), 'tahun' => $this->input->post('year')))
                ->row_array();
            if (!$cla) {
                $data = [
                    'NRP' => $i_nrp,
                    'method_id' => $this->input->post('method_id'),
                    'tahun' => $this->input->post('year'),
                ];
                $success = $this->db->insert('comp_lvl_assess', $data);
                $cla_id = $this->db->insert_id();
            } else {
                $cla_id = $cla['id'];
            }

            $cla_cols = [];
            $cl_ids   = [];

            foreach ($nrp_i as $key => $val) {
                if (in_array($key, $cla_columns, true)) {
                    $cla_cols[$key] = $val;
                } else {
                    $cl_ids[$key] = $val;
                }
            }

            $this->db->where('id', $cla_id)->update('comp_lvl_assess', $cla_cols);

            foreach ($cl_ids as $i_cl => $cl_i) {
                $clas = $this->db->get_where('comp_lvl_assess_score clas', array('comp_lvl_assess_id' => $cla['id'], 'comp_lvl_id' => $i_cl))->row_array();
                if ($cl_i || $cl_i === 0) {
                    if ($clas) {
                        $success = $this->db->where('id', $clas['id'])->update('comp_lvl_assess_score', array('score' => $cl_i));
                    } else {
                        $success = $this->db->insert('comp_lvl_assess_score', array('comp_lvl_assess_id' => $cla_id, 'score' => $cl_i, 'comp_lvl_id' => $i_cl));
                    }
                } else {
                    if ($clas) {
                        $success = $this->db->where('id', $clas['id'])->delete('comp_lvl_assess_score');
                    }
                }
            }
        }

        return $success;
    }
}
