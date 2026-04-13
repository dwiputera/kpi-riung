<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_user_access extends CI_Model
{
    protected $table_roles            = 'roles';
    protected $table_menus            = 'menus';
    protected $table_role_menu_access = 'role_menu_access';
    protected $table_user_roles       = 'users_roles';
    protected $table_sso_users        = 'rml_sso_la.users';

    public function get_roles()
    {
        return $this->db
            ->order_by('name', 'ASC')
            ->get($this->table_roles)
            ->result();
    }

    public function get_menus()
    {
        return $this->db
            ->order_by('parent_id IS NULL', 'DESC', false)
            ->order_by('parent_id', 'ASC')
            ->order_by('`order`', 'ASC', false)
            ->order_by('name', 'ASC')
            ->get($this->table_menus)
            ->result();
    }

    public function get_role_menu_ids($role_id)
    {
        $rows = $this->db
            ->select('menu_id')
            ->from($this->table_role_menu_access)
            ->where('role_id', $role_id)
            ->get()
            ->result_array();

        return array_map(function ($row) {
            return (int) $row['menu_id'];
        }, $rows);
    }

    public function get_user_roles()
    {
        return $this->db
            ->select('
                ur.id,
                ur.NRP,
                ur.role_id,
                r.name AS role_name,
                u.FullName AS user_name
            ')
            ->from($this->table_user_roles . ' ur')
            ->join($this->table_roles . ' r', 'r.id = ur.role_id', 'left')
            ->join($this->table_sso_users . ' u', 'u.NRP = ur.NRP', 'left')
            ->order_by('ur.NRP', 'ASC')
            ->get()
            ->result();
    }

    public function search_users($keyword = '')
    {
        if ($keyword !== '') {
            $this->db->where('NRP', $keyword);
        }

        return $this->db
            ->select('NRP, FullName AS name')
            ->from($this->table_sso_users)
            ->order_by('NRP', 'ASC')
            ->get()
            ->result();
    }

    public function get_user_by_nrp($nrp)
    {
        return $this->db
            ->from($this->table_sso_users)
            ->where('NRP', $nrp)
            ->get()
            ->row();
    }

    public function save_role($data)
    {
        if (!empty($data['id'])) {
            return $this->db->where('id', $data['id'])->update($this->table_roles, [
                'name'        => $data['name'],
                'description' => $data['description'],
            ]);
        }

        return $this->db->insert($this->table_roles, [
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function delete_role($id)
    {
        $used = $this->db->where('role_id', $id)->count_all_results($this->table_user_roles);
        if ($used > 0) {
            return false;
        }

        $this->db->trans_begin();

        $this->db->where('role_id', $id)->delete($this->table_role_menu_access);
        $this->db->where('id', $id)->delete($this->table_roles);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    public function save_role_menu_access($role_id, $menu_ids = [])
    {
        $menu_ids = array_map('intval', $menu_ids);

        $this->db->trans_begin();

        $this->db->where('role_id', $role_id)->delete($this->table_role_menu_access);

        if (!empty($menu_ids)) {
            $insert = [];
            foreach ($menu_ids as $menu_id) {
                if ($menu_id > 0) {
                    $insert[] = [
                        'role_id' => $role_id,
                        'menu_id' => $menu_id
                    ];
                }
            }

            if (!empty($insert)) {
                $this->db->insert_batch($this->table_role_menu_access, $insert);
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    public function save_user_role($data)
    {
        if (!empty($data['id'])) {
            return $this->db->where('id', $data['id'])->update($this->table_user_roles, [
                'NRP'     => $data['nrp'],
                'role_id' => $data['role_id'],
            ]);
        }

        $exists = $this->db
            ->where('NRP', $data['nrp'])
            ->where('role_id', $data['role_id'])
            ->count_all_results($this->table_user_roles);

        if ($exists > 0) {
            return true;
        }

        return $this->db->insert($this->table_user_roles, [
            'NRP'     => $data['nrp'],
            'role_id' => $data['role_id'],
        ]);
    }

    public function delete_user_role($id)
    {
        return $this->db->where('id', $id)->delete($this->table_user_roles);
    }
}
