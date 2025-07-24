<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Access extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('m_access');
    }

    public function index()
    {
        $data['roles'] = $this->db->get('roles')->result();
        $data['menus'] = $this->db->get('menus')->result();

        // Load all permissions to build matrix [role_id][menu_id] = permissions array
        $permissions = $this->db->get('permissions')->result();

        // Build lookup
        $perm_lookup = [];
        foreach ($permissions as $p) {
            $perm_lookup[$p->role_id][$p->menu_id] = $p;
        }
        $data['perm_lookup'] = $perm_lookup;

        $this->load->view('admin/access', $data);
    }

    public function save()
    {
        // Process submitted permissions
        $roles = $this->input->post('roles');   // array of role ids
        $menus = $this->input->post('menus');   // array of menu ids

        foreach ($roles as $role_id) {
            foreach ($menus as $menu_id) {
                // Get checkbox values (each will be '1' if checked, else null)
                $create = $this->input->post("perm_{$role_id}_{$menu_id}_create") ? 1 : 0;
                $read = $this->input->post("perm_{$role_id}_{$menu_id}_read") ? 1 : 0;
                $update = $this->input->post("perm_{$role_id}_{$menu_id}_update") ? 1 : 0;
                $delete = $this->input->post("perm_{$role_id}_{$menu_id}_delete") ? 1 : 0;

                // Use model to insert/update permission
                $this->m_access->set_permission($role_id, $menu_id, $create, $read, $update, $delete);
            }
        }

        $this->session->set_flashdata('success', 'Permissions updated successfully.');
        redirect('admin/access');
    }
}
