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
        $sql = "
            WITH RECURSIVE matrix_point_resolve AS (
                SELECT 
                    oalp.id AS start_id,
                    oalp.id AS current_id,
                    oalp.parent,
                    oalp.matrix_point,
                    oalp.name,
                    oalp.type,
                    CASE
                        WHEN oalp.type = 'matrix_point' THEN oalp.name
                        ELSE NULL
                    END AS matrix_point_name,
                    0 AS depth
                FROM org_area_lvl_pstn oalp

                UNION ALL

                SELECT 
                    m.start_id,
                    o.id,
                    o.parent,
                    o.matrix_point,
                    o.name,
                    o.type,
                    CASE
                        WHEN o.type = 'matrix_point' THEN o.name
                        ELSE m.matrix_point_name
                    END AS matrix_point_name,
                    m.depth + 1
                FROM matrix_point_resolve m
                JOIN org_area_lvl_pstn o 
                    ON o.id = m.parent OR o.id = m.matrix_point
                WHERE m.matrix_point_name IS NULL
            ),

            final_matrix_point AS (
                SELECT 
                    start_id AS node_id,
                    current_id AS mp_id,
                    matrix_point_name
                FROM (
                    SELECT 
                        start_id, current_id,
                        matrix_point_name,
                        ROW_NUMBER() OVER (PARTITION BY start_id ORDER BY depth ASC) AS rn
                    FROM matrix_point_resolve
                    WHERE matrix_point_name IS NOT NULL
                ) ranked
                WHERE rn = 1
            )
            SELECT 
                oalp.*, 
                oalp.id AS id, 
                oalp.name AS name, 
                oalp.parent AS oalp_parent,
                oal.id AS oal_id, 
                oal.name AS oal_name,
                oa.id AS oa_id, 
                oa.name AS oa_name,
                fmp.matrix_point_name AS mp_name,
                fmp.mp_id
            FROM org_area_lvl_pstn oalp
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
        ";

        // Tambahan filter
        if (is_array($value) && !empty($value)) {
            // Buat list string aman
            $escapedVals = array_map(function ($v) {
                return "'" . $this->db->escape_str($v) . "'";
            }, $value);

            $sql .= " WHERE $by IN (" . implode(",", $escapedVals) . ")";
        } elseif ($value !== null) {
            $sql .= " WHERE $by = " . $this->db->escape($value);
        }

        $query = $this->db->query($sql);

        return $many ? $query->result_array() : $query->row_array();
    }


    public function get_area_lvl_pstn_user($value = null, $by = 'md5(oalp.id)', $many = true)
    {
        $sql = "
            WITH RECURSIVE matrix_point_resolve AS (
                SELECT 
                    oalp.id AS start_id,
                    oalp.id AS current_id,
                    oalp.parent,
                    oalp.matrix_point,
                    oalp.name,
                    oalp.type,
                    CASE
                        WHEN oalp.type = 'matrix_point' THEN oalp.name
                        ELSE NULL
                    END AS matrix_point_name,
                    0 AS depth
                FROM org_area_lvl_pstn oalp

                UNION ALL

                SELECT 
                    m.start_id,
                    o.id,
                    o.parent,
                    o.matrix_point,
                    o.name,
                    o.type,
                    CASE
                        WHEN o.type = 'matrix_point' THEN o.name
                        ELSE m.matrix_point_name
                    END AS matrix_point_name,
                    m.depth + 1
                FROM matrix_point_resolve m
                JOIN org_area_lvl_pstn o 
                    ON o.id = m.parent OR o.id = m.matrix_point
                WHERE m.matrix_point_name IS NULL
            ),

            final_matrix_point AS (
                SELECT 
                    start_id AS node_id,
                    current_id AS mp_id,
                    matrix_point_name
                FROM (
                    SELECT 
                        start_id, current_id,
                        matrix_point_name,
                        ROW_NUMBER() OVER (PARTITION BY start_id ORDER BY depth ASC) AS rn
                    FROM matrix_point_resolve
                    WHERE matrix_point_name IS NOT NULL
                ) ranked
                WHERE rn = 1
            )
            SELECT 
                u.FullName,
                oalpu.*, oalpu.id AS oalpu_id,
                oalp.*, oalp.id AS oalp_id, oalp.parent AS oalp_parent, oalp.name AS oalp_name,
                oal.id AS oal_id, oal.name AS oal_name,
                oa.id AS oa_id, oa.name AS oa_name,
                fmp.matrix_point_name AS mp_name,
                fmp.mp_id
            FROM rml_sso_la.users u
            LEFT JOIN org_area_lvl_pstn_user oalpu ON oalpu.NRP = u.NRP
            LEFT JOIN org_area_lvl_pstn oalp ON oalp.id = oalpu.area_lvl_pstn_id
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            LEFT JOIN final_matrix_point fmp ON fmp.node_id = oalp.id
        ";

        // Filtering
        $binds = [];
        if (is_array($value) && !empty($value)) {
            $placeholders = implode(',', array_fill(0, count($value), '?'));
            $sql .= " WHERE {$by} IN ($placeholders)";
            $binds = $value;
        } elseif ($value !== null) {
            $sql .= " WHERE {$by} = ?";
            $binds[] = $value;
        }

        $query = $this->db->query($sql, $binds);

        return ($many) ? $query->result_array() : $query->row_array();
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

        // filter hasil akhir
        $extraWhere = '';
        if ($mode === 'without_matrix') {
            $extraWhere = "WHERE oalp.matrix_point IS NULL AND has_matrix_point = 0";
        } elseif ($mode === 'with_without_matrix') {
            // tampilkan semua KECUALI node 'matrix_point' selain root
            $extraWhere = "WHERE NOT (COALESCE(oalp.type,'') = 'matrix_point' AND oalp.id <> oalp.root_id)";
        }

        $query = "
            WITH RECURSIVE positions AS (
                -- Anchor: root
                SELECT
                    o.*,
                    CASE WHEN o.matrix_point IS NULL THEN 0 ELSE 1 END AS has_matrix_point,
                    0 AS level,
                    0 AS path_has_other_matrix,   -- tidak dipakai lagi untuk 'type', tetap dibiarkan 0
                    o.id AS root_id               -- <== bawa id root
                FROM org_area_lvl_pstn o
                WHERE md5(o.id) = '$hash_pstn_id'

                UNION ALL

                -- Recursion
                SELECT
                    c.*,
                    CASE WHEN c.matrix_point IS NOT NULL OR p.has_matrix_point = 1 THEN 1 ELSE 0 END AS has_matrix_point,
                    p.level + 1,
                    p.path_has_other_matrix,
                    p.root_id
                FROM org_area_lvl_pstn c
                INNER JOIN positions p ON (
                    -- Mode 1: parent only
                    ($modeValue = 1 AND c.parent = p.id)
                OR -- Mode 2: parent or matrix
                    ($modeValue = 2 AND (c.parent = p.id OR c.matrix_point = p.id))
                OR -- Mode 3: parent or matrix (matrix hanya NULL atau langsung ke root)
                    ($modeValue = 3 AND (c.parent = p.id OR c.matrix_point = p.id)
                        AND (c.matrix_point IS NULL OR md5(c.matrix_point) = '$hash_pstn_id')
                        -- BLOK node 'matrix_point' selain root (stop traversal di bawahnya)
                        AND NOT (COALESCE(c.type,'') = 'matrix_point' AND c.id <> p.root_id)
                    )
                OR -- Mode 4: parent only & tidak lewat jalur yg sudah kena matrix
                    ($modeValue = 4 AND c.parent = p.id AND p.has_matrix_point = 0)
                )
            )
            SELECT
                oalp.*,
                oalp.id   AS id,
                oalp.name AS name,
                oalp.matrix_point,
                oal.id    AS oal_id,
                oal.name  AS oal_name,
                oal.equals,
                oa.name   AS oa_name,
                oalp.root_id
            FROM positions oalp
            LEFT JOIN org_area_lvl oal ON oal.id = oalp.area_lvl_id
            LEFT JOIN org_area oa ON oa.id = oalp.area_id
            $extraWhere
        ";

        return $this->db->query($query)->result_array();
    }
}
