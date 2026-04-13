<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_access extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_user_access', 'm_user_access');
        $this->load->helper(['url', 'form']);
        $this->load->library(['session', 'form_validation']);
    }

    public function index()
    {
        $data['title'] = 'User Access';
        $data['roles'] = $this->m_user_access->get_roles();
        $data['menus'] = $this->m_user_access->get_menus();
        $data['content'] = 'user_access';

        $this->load->view('templates/header_footer', $data);
    }

    public function get_role_menu_access()
    {
        $role_id = (int) $this->input->post('role_id');
        $result = $this->m_user_access->get_role_menu_ids($role_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data'   => $result
            ]));
    }

    public function get_user_roles()
    {
        $rows = $this->m_user_access->get_user_roles();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data'   => $rows
            ]));
    }

    public function search_user()
    {
        $keyword = trim($this->input->get('q', true));
        $rows = $this->m_user_access->search_users($keyword);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data'   => $rows
            ]));
    }

    public function save_role()
    {
        $id          = (int) $this->input->post('id');
        $name        = trim($this->input->post('name', true));
        $description = trim($this->input->post('description', true));

        if ($name === '') {
            return $this->_json(false, 'Role name wajib diisi.');
        }

        $save = $this->m_user_access->save_role([
            'id'          => $id,
            'name'        => $name,
            'description' => $description,
        ]);

        if (!$save) {
            return $this->_json(false, 'Gagal menyimpan role.');
        }

        return $this->_json(true, 'Role berhasil disimpan.');
    }

    public function delete_role()
    {
        $id = (int) $this->input->post('id');

        if ($id <= 0) {
            return $this->_json(false, 'ID role tidak valid.');
        }

        $delete = $this->m_user_access->delete_role($id);

        if (!$delete) {
            return $this->_json(false, 'Role gagal dihapus. Pastikan tidak dipakai di mapping.');
        }

        return $this->_json(true, 'Role berhasil dihapus.');
    }

    public function save_role_menu_access()
    {
        $role_id  = (int) $this->input->post('role_id');
        $menu_ids = $this->input->post('menu_ids');

        if ($role_id <= 0) {
            return $this->_json(false, 'Role wajib dipilih.');
        }

        if (!is_array($menu_ids)) {
            $menu_ids = [];
        }

        $save = $this->m_user_access->save_role_menu_access($role_id, $menu_ids);

        if (!$save) {
            return $this->_json(false, 'Gagal menyimpan akses menu.');
        }

        return $this->_json(true, 'Akses menu berhasil disimpan.');
    }

    public function save_user_role()
    {
        $id      = (int) $this->input->post('id');
        $nrp     = trim($this->input->post('nrp', true));
        $role_id = (int) $this->input->post('role_id');

        if ($nrp === '') {
            return $this->_json(false, 'NRP wajib diisi.');
        }

        if ($role_id <= 0) {
            return $this->_json(false, 'Role wajib dipilih.');
        }

        $user = $this->m_user_access->get_user_by_nrp($nrp);
        if (!$user) {
            return $this->_json(false, 'NRP tidak ditemukan di rml_sso_la.users.');
        }

        $save = $this->m_user_access->save_user_role([
            'id'      => $id,
            'nrp'     => $nrp,
            'role_id' => $role_id,
        ]);

        if (!$save) {
            return $this->_json(false, 'Gagal menyimpan user role.');
        }

        return $this->_json(true, 'User role berhasil disimpan.');
    }

    public function delete_user_role()
    {
        $id = (int) $this->input->post('id');

        if ($id <= 0) {
            return $this->_json(false, 'ID mapping tidak valid.');
        }

        $delete = $this->m_user_access->delete_user_role($id);

        if (!$delete) {
            return $this->_json(false, 'Gagal menghapus user role.');
        }

        return $this->_json(true, 'User role berhasil dihapus.');
    }

    private function _json($status, $message = '', $data = [])
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => $status,
                'message' => $message,
                'data'    => $data
            ]));
    }
}