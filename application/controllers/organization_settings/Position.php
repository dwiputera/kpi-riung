<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Position extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('organization/m_area', 'm_area');
        $this->load->model('organization/m_level', 'm_lvl');
        $this->load->model('organization/m_position', 'm_pstn');
    }

    public function index()
    {
        $data['users'] = $this->m_pstn->get_users();
        $data['area'] = $this->m_area->get_area();
        $data['area_lvl'] = $this->m_lvl->get_area_lvl();
        $data['area_lvl_pstn'] = $this->get_area_lvl_pstn();
        $data['pstn_active_ids'] = [];
        if ($this->input->get('pstn_active')) {
            $pstn_active = $this->m_pstn->get_superiors($this->input->get('pstn_active'));
            $data['pstn_active_ids'] = array_column($pstn_active, 'id');
        }
        $data['matrix_points'] = $this->m_pstn->get_area_lvl_pstn('matrix_point', 'type');
        $data['area_lvl'] = $this->m_lvl->get_area_lvl();
        $data['content'] = "organization/position";
        $this->load->view('templates/header_footer', $data);
    }

    function get_area_lvl_pstn()
    {
        $area_lvl_pstn = $this->m_pstn->get_area_lvl_pstn();
        $area_lvl_pstn_user = $this->m_pstn->get_area_lvl_pstn_user();

        // Pastikan nilai '\N' atau string kosong diubah menjadi null
        foreach ($area_lvl_pstn as &$row) {
            if ($row['oalp_parent'] === '\\N' || $row['oalp_parent'] === '' || $row['oalp_parent'] === null) {
                $row['oalp_parent'] = null;
            }
            if ($row['matrix_point'] === '\\N' || $row['matrix_point'] === '' || $row['matrix_point'] === null) {
                $row['matrix_point'] = null;
            }
        }

        // Fungsi rekursif untuk membentuk struktur pohon
        function buildTree(array $elements, $area_lvl_pstn_user, $parentId = null)
        {
            $branch = [];

            foreach ($elements as $element) {
                if ($element['oalp_parent'] === $parentId) {
                    $element['users'] = array_filter($area_lvl_pstn_user, fn($alpu_i, $i_alpu) => $alpu_i['area_lvl_pstn_id'] == $element['id'], ARRAY_FILTER_USE_BOTH);
                    $children = buildTree($elements, $area_lvl_pstn_user, $element['id']);
                    $element['children'] = [];
                    if ($children) {
                        $element['children'] = $children;
                    }
                    $children_mp = buildTree_mp($elements, $area_lvl_pstn_user, $element['id']);
                    $element['children_mp'] = [];
                    if ($children_mp) {
                        $element['children_mp'] = $children_mp;
                    }
                    $branch[] = $element;
                }
            }

            return $branch;
        }

        function buildTree_mp(array $elements, $area_lvl_pstn_user, $parentId = null)
        {
            $branch = [];

            foreach ($elements as $element) {
                if ($element['matrix_point'] === $parentId) {
                    $element['users'] = array_filter($area_lvl_pstn_user, fn($alpu_i, $i_alpu) => $alpu_i['area_lvl_pstn_id'] == $element['id'], ARRAY_FILTER_USE_BOTH);
                    $children = buildTree($elements, $area_lvl_pstn_user, $element['id']);
                    $element['children'] = [];
                    if ($children) {
                        $element['children'] = $children;
                    }
                    $children_mp = buildTree_mp($elements, $area_lvl_pstn_user, $element['id']);
                    $element['children_mp'] = [];
                    if ($children_mp) {
                        $element['children_mp'] = $children_mp;
                    }
                    $branch[] = $element;
                }
            }

            return $branch;
        }

        $alpstn = buildTree($area_lvl_pstn, $area_lvl_pstn_user);
        return $alpstn;
    }

    public function add()
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Position Add Failed"
        ]);
        $pstn_id = $this->m_pstn->add();
        $data['pstn_active'] = $this->m_pstn->get_superiors(md5($pstn_id));
        if ($pstn_id) {
            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "Position Added Successfully"
            ]);
        }
        redirect('organization_settings/position?pstn_active=' . md5($pstn_id));
    }

    public function update()
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Position Update Failed"
        ]);
        $success = $this->m_pstn->update();
        if ($success) {
            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "Position Updateed Successfully"
            ]);
        }
        redirect('organization_settings/position?pstn_active=' . $this->input->post('position_id'));
    }

    public function delete($hash_id)
    {
        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Position Delete Failed"
        ]);
        $pstn_user = $this->db->get_where('org_area_lvl_pstn_user', array('md5(area_lvl_pstn_id)' => $hash_id))->row_array();
        if (!$pstn_user) {
            $pstn_id = $this->m_pstn->get_superiors($hash_id);
            $success = $this->m_pstn->delete($hash_id);

            if ($success) {
                $this->session->set_flashdata('swal', [
                    'type' => 'success',
                    'message' => "Position Deleted Successfully"
                ]);
            }

            $hash_pstn_id = null;
            if (isset($pstn_id[1])) {
                $hash_pstn_id = md5($pstn_id[1]['id']);
            }
            redirect('organization_settings/position?pstn_active=' . $hash_pstn_id);
        } else {
            $this->session->set_flashdata('swal', [
                'type' => 'error',
                'message' => "Please Unassign the User First"
            ]);
            redirect('organization_settings/position?pstn_active=' . $hash_id);
        }
    }

    public function position_user($action, $hash_id = null)
    {
        switch ($action) {
            case 'add':
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => "User Add Failed"
                ]);
                $success = $this->m_pstn->position_user("add");
                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "User Added Successfully"
                    ]);
                }
                redirect('organization_settings/position?pstn_active=' . $this->input->post('position_id'));
                break;

            case 'delete':
                $area_lvl_pstn_id = $this->db->get_where('org_area_lvl_pstn_user', array('md5(id)' => $hash_id))->row_array()['area_lvl_pstn_id'];
                $this->session->set_flashdata('swal', [
                    'type' => 'error',
                    'message' => "User Delete Failed"
                ]);
                $success = $this->m_pstn->position_user("delete", $hash_id);
                if ($success) {
                    $this->session->set_flashdata('swal', [
                        'type' => 'success',
                        'message' => "User Deleted Successfully"
                    ]);
                }
                redirect('organization_settings/position?pstn_active=' . md5($area_lvl_pstn_id));
                break;

            default:
                show_404();
                break;
        }
    }
}
