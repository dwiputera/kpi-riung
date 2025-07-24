<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_role extends CI_Model
{
    public function get_all()
    {
        return $this->db->get('roles')->result_array();
    }

    public function get_user_roles($NRP)
    {
        return $this->db->select('roles.*')
            ->from('users_roles')
            ->join('roles', 'roles.id = users_roles.role_id')
            ->where('users_roles.NRP', $NRP)
            ->or_where('roles.id', 0)
            ->get()->result_array();
    }

    public function get_role_permissions($role_id)
    {
        return $this->db->select('menu_id, permission_id')
            ->from('role_menu_access')
            ->where('role_id', $role_id)
            ->get()->result_array();
    }
}
