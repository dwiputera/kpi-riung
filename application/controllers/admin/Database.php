<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Database extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        $data['tables'] = $this->db->list_tables();
        $data['content'] = "admin/database";
        $this->load->view('templates/header_footer', $data);
    }

    public function table($table_id)
    {
        $tables = $this->db->list_tables();
        $table = $tables[$table_id];

        $data['table_id'] = $table_id;
        $data['table'] = $table;
        $data['columns'] = $this->db->list_fields($table);
        $data['rows'] = $this->db->get($table)->result_array();
        $data['content'] = "admin/table";
        $this->load->view('templates/header_footer', $data);
    }

    public function table_submit($table_id)
    {
        $success = false;
        $payload = json_decode($this->input->post('json_data'), true);
        $tables = $this->db->list_tables();
        $table = $tables[$table_id];

        $updates = $payload['updates'] ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $payload['creates'] ?? [];

        // UPDATES
        if (!empty($updates)) {
            $ids = array_column($this->db->select('id')->get($table)->result_array(), 'id');
            $updateData = [];
            foreach ($updates as $row) {
                if (isset($row['id']) && is_numeric($row['id']) && in_array($row['id'], $ids)) {
                    $updateData[] = $row;
                }
            }
            if (!empty($updateData)) {
                $this->db->update_batch($table, $updateData, 'id');
                $success = true;
            }
        }

        // DELETES
        if (!empty($deletes)) {
            $this->db->where_in('id', $deletes)->delete($table);
            $success = true;
        }

        // 3. Handle CREATES (new rows)
        if (!empty($creates)) {
            // Remove any rows marked as deleted
            $creates = array_filter($creates, function ($row) use ($deletes) {
                return !(isset($row['id']) && in_array($row['id'], $deletes));
            });

            $createData = [];
            foreach ($creates as $row) {
                if (isset($row['id']) && strpos($row['id'], 'new_') === 0) {
                    unset($row['id']);
                    $createData[] = $row;
                }
            }

            if (!empty($createData)) {
                $this->db->insert_batch($table, $createData);
                $success = true;
            }
        }

        $this->session->set_flashdata('swal', [
            'type' => 'error',
            'message' => "Data Submission Failed"
        ]);

        if ($success) {
            $this->session->set_flashdata('swal', [
                'type' => 'success',
                'message' => "Data Submitted Successfully"
            ]);
        }

        redirect("admin/database/table/$table_id");
    }
}
