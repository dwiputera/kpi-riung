<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Matrix_point extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('employee/m_employee', 'm_emp');
        $this->load->model('organization/m_position', 'm_pstn');
        $this->load->model('organization/m_user', 'm_user');
    }

    public function index()
    {
        $data['matrix_points'] = $this->m_pstn->get_area_lvl_pstn('matrix_point', 'type');
        $data['positions'] = $this->m_pstn->get_area_lvl_pstn(null, 'type');
        $data['content'] = "organization/matrix_point";
        $this->load->view('templates/header_footer', $data);
    }

    public function transfer_matrix_point()
    {
        if ($this->input->method() !== 'post') show_error('Invalid method', 405);

        $mode      = $this->input->post('mode', true) ?: 'transfer';
        $source_id = (int) $this->input->post('source_id', true);
        $target_id = (int) $this->input->post('target_id', true);

        if (!$source_id) {
            $this->session->set_flashdata('swal', ['type' => 'warning', 'message' => 'Source (posisi awal) tidak valid']);
            return redirect('organization_settings/matrix_point');
        }
        if ($mode === 'transfer' && !$target_id) {
            $this->session->set_flashdata('swal', ['type' => 'warning', 'message' => 'Pilih posisi tujuan dulu']);
            return redirect('organization_settings/matrix_point');
        }
        if ($mode === 'transfer' && $source_id === $target_id) {
            $this->session->set_flashdata('swal', ['type' => 'info', 'message' => 'Posisi tujuan sama dengan sumber. Tidak ada perubahan.']);
            return redirect('organization_settings/matrix_point');
        }

        // Daftar tabel/kolom yang mengacu ke org_area_lvl_pstn.id
        // Tambahkan sesuai kebutuhan.
        $fkRefs = [
            ['table' => 'comp_position',        'column' => 'area_lvl_pstn_id'],
            // ['table' => 'tabel_lain',       'column' => 'area_lvl_pstn_id'],
        ];

        $this->db->trans_start();

        // Kunci baris sumber/tujuan supaya aman dari race condition
        $this->db->query('SELECT id FROM org_area_lvl_pstn WHERE id IN (?, ?) FOR UPDATE', [$source_id, ($target_id ?: 0)]);

        // Pastikan source ada
        $src = $this->db->select('id,type')->get_where('org_area_lvl_pstn', ['id' => $source_id], 1)->row_array();
        if (!$src) {
            $this->db->trans_complete();
            $this->session->set_flashdata('swal', ['type' => 'error', 'message' => 'Source tidak ditemukan']);
            return redirect('organization_settings/matrix_point');
        }

        // 1) NULL-kan type di posisi sumber
        $this->db->where('id', $source_id)->update('org_area_lvl_pstn', ['type' => NULL]);

        if ($mode === 'transfer') {
            // Pastikan target ada
            $tgt = $this->db->select('id,type')->get_where('org_area_lvl_pstn', ['id' => $target_id], 1)->row_array();
            if (!$tgt) {
                $this->db->trans_complete();
                $this->session->set_flashdata('swal', ['type' => 'error', 'message' => 'Target tidak ditemukan']);
                return redirect('organization_settings/matrix_point');
            }

            // 2) Jadikan target sebagai matrix_point
            $this->db->where('id', $target_id)->update('org_area_lvl_pstn', ['type' => 'matrix_point']);

            // 3) Pindahkan semua referensi FK dari source -> target
            //    Set $dedup = true kalau ingin aman dari duplikasi (disarankan)
            $dedup = true;
            $this->_move_fk_refs($source_id, $target_id, $fkRefs, $dedup);
        }
        // mode 'unassign': FK tidak diubah; hanya source type = NULL.

        $this->db->trans_complete();
        $ok = $this->db->trans_status();

        if ($ok) {
            $msg = ($mode === 'transfer')
                ? 'Matrix point + referensi terkait berhasil ditransfer'
                : 'Matrix point pada posisi sumber berhasil di-unassign';
            $this->session->set_flashdata('swal', ['type' => 'success', 'message' => $msg]);
        } else {
            $this->session->set_flashdata('swal', ['type' => 'error', 'message' => 'Gagal memproses transfer/unassign matrix point']);
        }

        return redirect('organization_settings/matrix_point');
    }

    /**
     * Pindahkan nilai FK dari $source_id ke $target_id untuk setiap tabel/kolom pada $fkRefs.
     * Jika $dedup = true, lakukan deduplikasi baris “kembar” setelah update (berdasarkan seluruh kolom selain id & FK).
     * $fkRefs: array seperti [ ['table'=>'comp_pstn', 'column'=>'area_lvl_pstn_id'], ... ]
     */
    private function _move_fk_refs(int $source_id, int $target_id, array $fkRefs, bool $dedup = true): void
    {
        foreach ($fkRefs as $ref) {
            $table = $ref['table'];
            $col   = $ref['column'];

            // 3a) UPDATE langsung: ganti source -> target
            $this->db->where($col, $source_id)
                ->set($col, $target_id)
                ->update($table);

            // 3b) DEDUP (opsional): hilangkan baris kembar di target
            if ($dedup) {
                $this->_dedup_table_by_all_columns($table, $col, $target_id);
            }
        }
    }

    /**
     * Hapus duplikasi di $table untuk baris dengan $fkColumn = $target_id,
     * dengan cara menyisakan 1 baris (id terkecil) untuk setiap kombinasi nilai kolom-kolom lain.
     * Kolom yang diabaikan saat membandingkan: id, $fkColumn, created_at, updated_at, deleted_at.
     */
    private function _dedup_table_by_all_columns(string $table, string $fkColumn, int $target_id): void
    {
        // Ambil daftar kolom tabel dari INFORMATION_SCHEMA
        $cols = $this->db->query("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
        ORDER BY ORDINAL_POSITION
    ", [$table])->result_array();

        $allCols = array_map(function ($r) {
            return $r['COLUMN_NAME'];
        }, $cols);
        // Kolom yang jangan dibandingkan
        $exclude = ['id', $fkColumn, 'created_at', 'updated_at', 'deleted_at'];

        // Filter kolom pembanding
        $cmpCols = array_values(array_diff($allCols, $exclude));

        if (empty($cmpCols)) {
            // Tidak ada kolom yang bisa dibandingkan; tidak aman menghapus apa pun.
            return;
        }

        // Build kondisi NULL-safe: t1.col <=> t2.col
        $onParts = array_map(function ($c) {
            return " (t1.`{$c}` <=> t2.`{$c}`) ";
        }, $cmpCols);
        $onClause = implode(' AND ', $onParts);

        // Hapus duplikat: yang id lebih besar dihapus
        $sql = "
        DELETE t1 FROM `{$table}` t1
        JOIN `{$table}` t2
          ON t1.id > t2.id
         AND t1.`{$fkColumn}` = t2.`{$fkColumn}`
         AND {$onClause}
        WHERE t1.`{$fkColumn}` = ?
    ";
        $this->db->query($sql, [$target_id]);
    }
}
