<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_permission extends CI_Model
{
    function has_permission()
    {
        $NRP = $this->session->userdata('NRP');
        $menu_url = $this->uri->segment(1);
        if ($menu_url == "auth" || $menu_url == "") {
            return true;
        }
        $sub = $this->uri->segment(2);
        if ($sub) {
            $menu_url .= '/' . $sub;
        }

        $this->load->model(['m_role']);

        // 1. Get user roles
        $roles = $this->m_role->get_user_roles($NRP);
        $role_ids = array_column($roles, 'id');
        if (empty($role_ids)) return false;

        // 2. Get the menu ID for the given URL
        $menu = $this->db->get_where('menus', ['url' => $menu_url])->row_array();
        if (!$menu) return false;

        $this->db->from('role_menu_access');
        $this->db->where_in('role_id', $role_ids);
        $this->db->where('menu_id', $menu['id']);
        $count = $this->db->count_all_results();

        return $count > 0;
    }
}
