<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Database extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // Tambahan:
        $this->load->dbutil();
        $this->load->helper(['download', 'cookie']);
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

    // ====== NEW: Download full DB sebagai .sql ======
    public function download()
    {
        // Dump SQL (struktur + data)
        $prefs = [
            'format'     => 'txt',
            'filename'   => 'backup.sql',
            'add_drop'   => TRUE,
            'add_insert' => TRUE,
            'newline'    => "\n",
        ];
        $sql = $this->dbutil->backup($prefs);

        // Deteksi environment lokal vs server
        $server_name = $_SERVER['SERVER_NAME'] ?? '';
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
        $is_local = (
            stripos($server_name, 'localhost') !== false ||
            $remote_addr === '127.0.0.1' ||
            $remote_addr === '::1'
        ) || (defined('ENVIRONMENT') && ENVIRONMENT === 'development');

        $backup_prefix = $is_local ? 'rml_kpi_hcla_' : 'rml_kpi_hcla_server_';

        // Nama file: YYMMDD_hhiiss.sql
        $fname = $backup_prefix . date('ymd_H-i-s') . '.sql';

        // Token dari query (untuk hide overlay di front-end)
        $token = $this->input->get('t', TRUE);
        if ($token) {
            // Cookie 2 menit cukup
            set_cookie('downloadToken', $token, 120, '', '/', '', FALSE, FALSE);
        }

        // Kirim file
        force_download($fname, $sql);
    }

    function emptyStringToNull($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'emptyStringToNull'], $data);
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->emptyStringToNull($v);
            }
            return $data;
        }
        return $data === '' ? null : $data;
    }

    public function table_submit($table_id)
    {
        $success = false;
        $payload = json_decode($this->input->post('json_data'), true);
        $tables = $this->db->list_tables();
        $table = $tables[$table_id];

        $updates = $this->emptyStringToNull($payload['updates']) ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $this->emptyStringToNull($payload['creates']) ?? [];

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

        // CREATES
        if (!empty($creates)) {
            $creates = array_filter($creates, function ($row) use ($deletes) {
                return !(isset($row['id']) && in_array($row['id'], $deletes));
            });

            $createData = [];
            foreach ($creates as $row) {
                // jika id tidak ada / kosong / bukan numeric / diawali new_
                $isNew = !isset($row['id']) || $row['id'] === '' || $row['id'] === null || (is_string($row['id']) && strpos($row['id'], 'new_') === 0) || !is_numeric($row['id']);
                if ($isNew) {
                    unset($row['id']);
                    $createData[] = $row;
                }
            }

            if (!empty($createData)) {
                $this->db->insert_batch($table, $createData);
                $success = true;
            }
        }

        flash_swal('error', 'Data Submission Failed');
        if ($success) {
            flash_swal('success', 'Data Submitted Successfully');
        }

        redirect("admin/database/table/$table_id");
    }
}
