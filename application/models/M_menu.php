<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_menu extends CI_Model
{
    public function get_menu_by_url($url)
    {
        return $this->db->get_where('menus', ['url' => $url])->row_array();
    }

    public function get_menu($NRP)
    {
        $this->load->model('m_role');
        $roles = $this->m_role->get_user_roles($NRP);
        $role_ids = array_column($roles, 'id');
        array_unshift($role_ids, 0);
        // if (empty($role_ids)) return [];

        $roles = $this->db->from('roles')->where_in('roles.id', $role_ids)->get()->result_array();

        foreach ($roles as $role_key => $role) {
            $this->db->distinct();
            $this->db->select('m.*');
            $this->db->from('menus m');
            $this->db->join('role_menu_access rma', 'm.id = rma.menu_id');
            $this->db->where('m.parent_id', null);
            $this->db->where_in('rma.role_id', $role['id']);
            $this->db->order_by('m.parent_id, m.order, m.id');
            $roles[$role_key]['menus'] = $this->db->get()->result_array();
            foreach ($roles[$role_key]['menus'] as $menu_key => $menu) {
                $this->db->distinct();
                $this->db->select('m.*');
                $this->db->from('menus m');
                $this->db->join('role_menu_access rma', 'm.id = rma.menu_id');
                $this->db->where('m.parent_id', $menu['id']);
                $this->db->where('rma.role_id', $role['id']);
                $this->db->order_by('m.parent_id, m.order, m.id');
                $roles[$role_key]['menus'][$menu_key]['children'] = $this->db->get()->result_array();
            }
        }
        return $roles;
    }
}
