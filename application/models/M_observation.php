<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_observation extends CI_Model
{
    protected $table = 'observations';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_observations($filters = [])
    {
        $this->db->select('
            o.*,
            a.name AS job_site_name,
            p.name AS pit_location_name,
            e.equipment AS unit_type_name,
            a.name as area_name
        ');
        $this->db->from($this->table . ' o');
        $this->db->join('org_area a', 'a.id = o.job_site', 'left');
        $this->db->join('org_area_pit p', 'p.id = o.pit_location', 'left');
        $this->db->join('equipments e', 'e.id = o.unit_type', 'left');

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.date) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.date) <=', $filters['date_to']);
        }

        return $this->db
            ->order_by('o.id', 'DESC')
            ->get()
            ->result_array();
    }

    public function get_observation_by_id($id)
    {
        return $this->db
            ->select('o.*')
            ->from($this->table . ' o')
            ->where('o.id', $id)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function insert($data)
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        $this->db->insert($this->table, $data);

        return ($this->db->affected_rows() > 0)
            ? $this->db->insert_id()
            : false;
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->update($this->table, $data);

        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);

        return $this->db->affected_rows() > 0;
    }
}
