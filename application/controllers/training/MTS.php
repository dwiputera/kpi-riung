<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MTS extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('training/m_mts');
        $this->load->model('training/m_mts_user', 'm_m_u');
        $this->load->model('training/m_atmp');
    }

    function index()
    {
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $data['trainings'] = $this->m_mts->get_mts($year, 'trn_mts.year');
        $data['chart_mts_atmp'] = $this->m_mts->get_mts_atmp_chart($year);
        $data['chart_mts_status'] = $this->m_mts->get_mts_status_chart($year);
        $data['matrix_points'] = $this->db->get_where('org_area_lvl_pstn', array('type' => 'matrix_point'))->result_array();
        $data['year'] = $year;
        $data['content'] = "training/MTS";
        $this->load->view('templates/header_footer', $data);
    }

    private function set_swal($type, $msg)
    {
        $this->session->set_flashdata('swal', ['type' => $type, 'message' => $msg]);
    }

    function edit($year)
    {
        $data['year'] = $year;
        $data['trainings'] = $this->m_mts->get_mts($year, 'trn_mts.year');
        $data['atmps'] = $this->m_atmp->get_atmp($year, 'trn_atmp.year');
        $data['matrix_points'] = $this->db->get_where('org_area_lvl_pstn', array('type' => 'matrix_point'))->result_array();
        $data['content'] = "training/MTS_edit";
        $data['advanced'] = $this->input->get('advanced');
        $this->load->view('templates/header_footer', $data);
    }

    public function submit()
    {
        if ($this->input->post('proceed') == 'N') {
            redirect('training/MTS?year=' . $this->input->post('year'));
        }

        $payload = json_decode($this->input->post('json_data'), true);
        $year = $this->input->post('year');

        $success = $this->m_mts->submit($payload, $year);

        $this->session->set_flashdata('swal', [
            'type' => $success ? 'success' : 'error',
            'message' => $success ? "MTS edited successfully" : "Failed to update MTS"
        ]);

        redirect('training/MTS/edit/' . $year);
    }

    public function ATMP($mts_hash)
    {
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $mts = $this->m_mts->get_mts($mts_hash, 'md5(trn_mts.id)', false);
        $atmp = null;
        if ($mts['atmp_id']) $atmp = $this->m_atmp->get_atmp($mts['atmp_id'], "id", false);
        if (!$this->input->get('action')) {
            $data['mts'] = $mts;
            $data['atmp'] = $atmp;
            $data['atmps'] = $this->m_atmp->get_atmp($mts['atmp_id'], "year = $year AND id !=");
            $data['year'] = $year;
            $data['content'] = "training/MTS_ATMP";
        } else {
            $atmp_hash = $this->input->get('atmp_hash');
            $atmp = $this->db->get_where('trn_atmp', array('md5(id)' => $atmp_hash))->row_array();
            if ($this->input->get('action') == 'unassign') {
                $this->set_swal('error', 'ATMP Unassign Failed');
                $success = $this->m_mts->submit(['updates' => [['id' => $mts['id'], 'atmp_id' => null]]], $year);
                if ($success) $this->set_swal('success', 'ATMP Unassigned Successfully');
            }
            if ($this->input->get('action') == 'assign') {
                $this->set_swal('error', 'ATMP Assign Failed');
                $success = $this->m_mts->submit(['updates' => [['id' => $mts['id'], 'atmp_id' => $atmp['id']]]], $year);
                if ($success) $this->set_swal('success', 'ATMP Assigned Successfully');
            }
            redirect('training/MTS/ATMP/' . $mts_hash . '?year=' . $year);
        }
        $this->load->view('templates/header_footer', $data);
    }

    public function participants($mts_hash)
    {
        $data['type'] = "mts";
        $year = $this->input->get('year');
        $year = $year ? $year : date('Y');
        $mts = $this->m_mts->get_mts($mts_hash, 'md5(trn_mts.id)', false);
        if (!$this->input->get('action')) {
            $data['mts'] = $mts;
            $data['participants'] = $this->m_m_u->get_mts_user($mts_hash, "md5(mts_id)");
            $data['year'] = $year;
            $this->load->model('organization/m_user');
            $data['users'] = $this->m_user->get_user();
            $data['content'] = "training/users";
        } else {
            if ($this->input->get('action') == 'assign') {
                $this->set_swal('error', 'Participants Assign Failed');
                $success = $this->m_m_u->add($mts['id']);
                if ($success) $this->set_swal('success', 'Participants Assigned Successfully');
            }
            if ($this->input->get('action') == 'status_change') {
                $this->set_swal('error', 'Participants Status Change Failed');
                $success = $this->m_m_u->status_change();
                if ($success) $this->set_swal('success', 'Participants Status Changeed Successfully');
            }
            redirect('training/MTS/participants/' . $mts_hash . '?year=' . $year);
        }
        $this->load->view('templates/header_footer', $data);
    }

    function fix()
    {
        $this->db->trans_begin();

        /*
        |----------------------------------------
        | 1. Ambil semua user yang punya duplicate
        |    berdasarkan NRP + nama_program
        |----------------------------------------
        */
        $duplicates = $this->db->query("
            SELECT 
                tmu.NRP,
                tm.nama_program
            FROM trn_mts_user tmu
            JOIN trn_mts tm ON tm.id = tmu.mts_id
            GROUP BY tmu.NRP, tm.nama_program
            HAVING COUNT(*) > 1
        ")->result_array();

        foreach ($duplicates as $dup) {

            $nrp = $dup['NRP'];
            $nama_program = $dup['nama_program'];

            /*
            |----------------------------------------
            | 2. Ambil semua MTS user tsb
            |    urutkan year & month ASC (terkecil = master)
            |----------------------------------------
            */
            $mts_list = $this->db->select('tm.id, tm.year, tm.month, tm.atmp_id')
                ->from('trn_mts_user tmu')
                ->join('trn_mts tm', 'tm.id = tmu.mts_id')
                ->where([
                    'tmu.NRP' => $nrp,
                    'tm.nama_program' => $nama_program
                ])
                ->order_by('tm.year', 'ASC')
                ->order_by('tm.month', 'ASC')
                ->get()
                ->result_array();

            if (count($mts_list) < 2) continue;

            $master = $mts_list[0];
            $masterId = $master['id'];

            /*
            |----------------------------------------
            | 3. Loop selain master
            |----------------------------------------
            */
            for ($i = 1; $i < count($mts_list); $i++) {

                $old = $mts_list[$i];
                $oldId = $old['id'];
                $oldAtmp = $old['atmp_id'];

                /*
                |----------------------------------------
                | 3a. Pastikan user belum ada di master
                |----------------------------------------
                */
                $exists = $this->db->select('id')
                    ->from('trn_mts_user')
                    ->where([
                        'mts_id' => $masterId,
                        'NRP' => $nrp
                    ])
                    ->limit(1)
                    ->get()
                    ->row_array();

                if (!$exists) {
                    $this->db->insert('trn_mts_user', [
                        'mts_id' => $masterId,
                        'NRP' => $nrp,
                        'status' => 'Y'
                    ]);
                }

                /*
                |----------------------------------------
                | 3b. Hapus relasi lama
                |----------------------------------------
                */
                $this->db->where([
                    'mts_id' => $oldId,
                    'NRP' => $nrp
                ])->delete('trn_mts_user');

                /*
                |----------------------------------------
                | 4. Cek apakah MTS lama masih punya user
                |----------------------------------------
                */
                $stillHasUser = $this->db->select('id')
                    ->from('trn_mts_user')
                    ->where('mts_id', $oldId)
                    ->limit(1)
                    ->get()
                    ->row_array();

                if (!$stillHasUser) {

                    /*
                    |----------------------------------------
                    | 5. Hapus trn_mts
                    |----------------------------------------
                    */
                    $this->db->where('id', $oldId)->delete('trn_mts');

                    /*
                    |----------------------------------------
                    | 6. Hapus trn_atmp jika ada
                    |----------------------------------------
                    */
                    if ($oldAtmp) {
                        $this->db->where('id', $oldAtmp)->delete('trn_atmp');
                    }
                }
            }
        }

        /*
        |----------------------------------------
        | Commit / Rollback
        |----------------------------------------
        */
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }

        die;

        // =======================================================

        $this->db->trans_begin();

        // 1) Ambil grup yang duplikat (tanpa select id biar gak perlu disable ONLY_FULL_GROUP_BY)
        $mts_grouped = $this->db
            ->select('nama_program, year, month, COUNT(*) AS total')
            ->from('trn_mts')
            ->where('year <', 2024)
            ->group_by(['nama_program', 'year', 'month'])
            ->having('COUNT(*) >', 1, false)
            ->get()
            ->result_array();

        foreach ($mts_grouped as $g) {

            // 2) Ambil semua rows trn_mts untuk grup ini (urutkan: master di paling atas)
            $mts_rows = $this->db
                ->select('id, atmp_id')
                ->from('trn_mts')
                ->where([
                    'nama_program' => $g['nama_program'],
                    'year'         => (int)$g['year'],
                    'month'        => (int)$g['month'],
                ])
                ->order_by('id', 'ASC') // master = id terkecil
                ->get()
                ->result_array();

            if (count($mts_rows) < 2) continue;

            $masterId = (int)$mts_rows[0]['id'];

            // 3) Untuk tiap duplikat selain master: pindahkan user -> master, lalu hapus duplikatnya
            for ($i = 1; $i < count($mts_rows); $i++) {

                $dupId  = (int)$mts_rows[$i]['id'];
                $atmpId = isset($mts_rows[$i]['atmp_id']) ? (int)$mts_rows[$i]['atmp_id'] : 0;

                // 3a) Ambil semua user pada dup mts_id
                $mts_users = $this->db
                    ->select('id, NRP')
                    ->from('trn_mts_user')
                    ->where('mts_id', $dupId)
                    ->get()
                    ->result_array();

                foreach ($mts_users as $u) {
                    $nrp = $u['NRP'];

                    // cek apakah NRP sudah ada di master
                    $exists = $this->db
                        ->select('id')
                        ->from('trn_mts_user')
                        ->where([
                            'mts_id' => $masterId,
                            'NRP'    => $nrp
                        ])
                        ->limit(1)
                        ->get()
                        ->row_array();

                    if (!$exists) {
                        $this->db->insert('trn_mts_user', [
                            'mts_id'  => $masterId,
                            'NRP'     => $nrp,
                            'status'  => 'Y', // atau ikutkan status lama kalau perlu
                        ]);
                    }

                    // hapus user lama dari dup
                    $this->db->where('id', (int)$u['id'])->delete('trn_mts_user');
                }

                // 3b) Hapus trn_mts duplikat
                $this->db->where('id', $dupId)->delete('trn_mts');

                // 3c) Hapus trn_atmp (opsional & hati-hati)
                // Kalau yakin atmp_id itu memang eksklusif untuk row ini:
                if ($atmpId > 0) {
                    // opsional safety: pastikan atmp_id tidak dipakai lagi oleh trn_mts lain
                    $stillUsed = $this->db
                        ->select('id')
                        ->from('trn_mts')
                        ->where('atmp_id', $atmpId)
                        ->limit(1)
                        ->get()
                        ->row_array();

                    if (!$stillUsed) {
                        $this->db->where('id', $atmpId)->delete('trn_atmp');
                    }
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            // logging biar ketahuan errornya
            log_message('error', 'Merge trn_mts duplicates failed: ' . $this->db->error()['message']);
        } else {
            $this->db->trans_commit();
        }
    }
}
