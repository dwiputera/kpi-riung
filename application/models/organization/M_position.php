<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_position extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_area_lvl_pstn($value = null, $by = 'md5(oalp.id)', $many = true)
    {
        $where = '';
        if ($value) $where = "WHERE $by = '$value'";
        $query = $this->db->query("
            SELECT *, 
                oalp.id id, oalp.name name, oalp.parent oalp_parent,
                oal.id oal_id, oal.name oal_name,
                oa.id oa_id, oa.name oa_name,
                oalp_mp.name mp_name
            FROM org_area_lvl_pstn oalp
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN (SELECT id, name FROM org_area_lvl_pstn) oalp_mp ON oalp_mp.id = oalp.matrix_point
            $where
        ");
        if (($value && !$many) || $many == false) {
            $query = $query->row_array();
        } else {
            $query = $query->result_array();
        }
        return $query;
    }

    public function get_area_lvl_pstn_user($hash_id = null)
    {
        $query = $this->db->query("
            SELECT org_area_lvl_pstn_user.*, users.FullName FROM org_area_lvl_pstn_user
            LEFT JOIN rml_sso_la.users ON users.NRP = org_area_lvl_pstn_user.NRP
        ")->result_array();
        if ($hash_id) {
            if ($query) {
                $query = array_values($query);
                if (isset($query[0])) $query = $query[0];
            }
        }
        return $query;
    }

    public function get_users()
    {
        $query = $this->db->query("
            SELECT FullName, NRP, PSubarea, EmployeeSubgroup, OrgUnitName, PositionName, EmployeeGroup
            FROM rml_sso_la.users
            WHERE NRP NOT IN (SELECT NRP FROM org_area_lvl_pstn_user)
            AND (
                users.EmployeeGroup != 'Terminated'
                OR users.ActionType != 'Terminate'
            )
        ")->result_array();
        return $query;
    }

    public function add()
    {
        $data['parent'] = null;
        if ($this->input->post('parent_key') != md5(0)) {
            $data['parent'] = $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $this->input->post('parent_key')))->row_array()['id'];
            $data['area_lvl_id'] = $this->db->get_where('org_area_lvl', array('md5(id)' => $this->input->post('area_lvl')))->row_array()['id'];
        } else {
            $data['area_lvl_id'] = $this->db->get_where('org_area_lvl', array('parent' => 0))->row_array()['id'];
        }
        if ($this->input->post('method') == 'automatic') {
            $area_id = $this->db->get_where('org_area', array('name' => $this->input->post('PSubarea')))->row_array()['id'];
            $employeesubgroup = $this->input->post('EmployeeSubgroup');
            $employeesubgroup = $employeesubgroup == 'Junior Staff' ? 'Officer HO/GroupLead' : $employeesubgroup;
            $data['area_lvl_id'] = $this->db->get_where('org_area_lvl', array('name' => $employeesubgroup, 'area_id' => $area_id))->row_array()['id'];
            $data['name'] = substr($this->input->post('OrgUnitName'), 5);
            if ($this->input->post('OrgUnitName') == 'PT Riung Mitra Lestari') {
                $data['name'] = $this->db->get_where('rml_sso_la.users', array('NRP' => $this->input->post('NRP')))->row_array()['PositionName'];
            }
            if ($employeesubgroup == 'Officer HO/GroupLead') {
                $data['name'] = substr($this->input->post('PositionName'),  18);
            }
        } else {
            $data['name'] = $this->input->post('position_name');
        }
        $success = $this->db->insert('org_area_lvl_pstn', $data);
        $insert_id = $this->db->insert_id();
        if ($this->input->post('method') == 'automatic') {
            $data = [
                'area_lvl_pstn_id' => $insert_id,
                'NRP' => $this->input->post('NRP'),
            ];
            $success = $this->db->insert('org_area_lvl_pstn_user', $data);
        }
        return $insert_id;
    }

    public function update()
    {
        $data_update = [];
        $success = false;
        $area_id = $this->db->get_where('org_area', array('md5(id)' => $this->input->post('area_id')))->row_array()['id'];
        $type = $this->input->post('type') == "on" ? 'matrix_point' : null;
        $data = [
            'id' => $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $this->input->post('position_id')))->row_array()['id'],
            'area_id' => $area_id,
            'area_lvl_id' => $this->db->get_where('org_area_lvl', array('md5(id)' => $this->input->post('area_lvl')))->row_array()['id'],
            'name' => $this->input->post('position_name'),
            'matrix_point' => $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $this->input->post('matrix_point')))->row_array()['id'],
            'type' => $type,
        ];

        if ($this->input->post('update_subordinate_area') == "on") {
            $data_update[] = $data;

            $subordinates = $this->m_pstn->get_subordinates($this->input->post('position_id'));
            foreach ($subordinates as $i_subor => $subor_i) {
                $data = [
                    'id' => $subor_i['id'],
                    'area_id' => $area_id,
                ];
                $data_update[] = $data;
            }

            if ($data_update) $success = $this->db->update_batch('org_area_lvl_pstn', $data_update, 'id');
        } else {
            $this->db->where('md5(id)', $this->input->post('position_id'));
            $success = $this->db->update('org_area_lvl_pstn', $data);
        }
        return $success;
    }

    public function delete($hash_id)
    {
        $this->db->where('md5(id)', $hash_id);
        $success = $this->db->delete('org_area_lvl_pstn');
        return $success;
    }

    public function position_user($action, $hash_id = null)
    {
        switch ($action) {
            case 'add':
                $data['area_lvl_pstn_id'] = $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $this->input->post('position_id')))->row_array()['id'];
                foreach ($this->input->post('NRP') as $NRP) {
                    $data['NRP'] = $NRP;
                    $success = $this->db->insert('org_area_lvl_pstn_user', $data);
                    if (!$success) {
                        break;
                    }
                }
                return $success;

            case 'delete':
                $this->db->where('md5(id)', $hash_id);
                $success = $this->db->delete('org_area_lvl_pstn_user');
                return $success;


            default:
                show_404();
                break;
        }
    }

    function get_superiors($hash_pstn_id)
    {
        $superiors = [];

        while (true) {
            $row = $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => $hash_pstn_id))->row_array();

            if (!$row || empty($row['parent'])) {
                break;
            }
            if (!$superiors) {
                $superiors[] = $row;
            }

            $superior_id = $row['parent'];
            $superior = $this->db->get_where('org_area_lvl_pstn', array('md5(id)' => md5($superior_id)))->row_array();
            $superiors[] = $superior;

            // Update hash for next iteration
            $hash_pstn_id = md5($superior_id);
        }

        return $superiors;
    }

    function get_subordinates($hash_pstn_id, $mode = 'basic')
    {
        $modeValue = [
            'basic' => 1,
            'with_matrix' => 2,
            'with_without_matrix' => 3,
            'without_matrix' => 4,
        ][$mode] ?? 1;

        $query = "
            WITH RECURSIVE positions AS (
                SELECT *, 
                       CASE WHEN matrix_point IS NULL THEN 0 ELSE 1 END AS has_matrix_point,
                       0 as level
                FROM org_area_lvl_pstn
                WHERE md5(id) = '$hash_pstn_id'
    
                UNION ALL
    
                SELECT o.*, 
                       CASE 
                           WHEN o.matrix_point IS NOT NULL OR p.has_matrix_point = 1 THEN 1
                           ELSE 0
                       END AS has_matrix_point,
                       p.level + 1
                FROM org_area_lvl_pstn o
                INNER JOIN positions p ON (
                    ($modeValue = 1 AND o.parent = p.id)
                 OR ($modeValue = 2 AND (o.parent = p.id OR o.matrix_point = p.id))
                 OR ($modeValue = 3 AND (o.parent = p.id OR o.matrix_point = p.id) AND (o.matrix_point IS NULL OR md5(o.matrix_point) = '$hash_pstn_id'))
                 OR ($modeValue = 4 AND o.parent = p.id AND p.has_matrix_point = 0)
                )
            )
            SELECT 
                oalp.*, 
                oalp.id AS id, 
                oalp.name AS name, 
                oalp.matrix_point,
                oal.id AS oal_id, 
                oal.name AS oal_name,
                oal.equals,
                oa.name AS oa_name
            FROM positions oalp
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            " . ($mode === 'without_matrix' ? "WHERE oalp.matrix_point IS NULL AND has_matrix_point = 0" : "");

        return $this->db->query($query)->result_array();
    }
}
