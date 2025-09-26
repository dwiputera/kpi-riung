<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employee_position extends MY_Controller
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
        $data['employees'] = $this->m_emp->get_employee();
        $data['positions'] = $this->m_pstn->get_area_lvl_pstn();
        $data['user'] = 'admin';
        $data['content'] = "employee/list";
        $this->load->view('templates/header_footer', $data);
    }

    // Controller: Employee.php
    public function assign_position()
    {
        // pastikan POST
        if ($this->input->method() !== 'post') show_error('Invalid method', 405);

        $mode        = $this->input->post('mode', true) ?: 'assign';
        $nrp         = trim($this->input->post('nrp', true));
        $position_id = $this->input->post('position_id', true);

        if (!$nrp) {
            $this->session->set_flashdata('swal', ['type' => 'warning', 'message' => 'NRP tidak valid']);
            return redirect('organization_settings/employee_position');
        }

        if ($mode === 'unassign') {
            // hapus record assignment (atau bisa juga set kolom ke NULL jika kamu mau men-trace rownya)
            $ok = $this->db->delete('org_area_lvl_pstn_user', ['NRP' => $nrp]);

            $this->session->set_flashdata('swal', [
                'type' => $ok ? 'success' : 'error',
                'message' => $ok ? 'Posisi berhasil di-unassign' : 'Gagal unassign posisi'
            ]);
            return redirect('organization_settings/employee_position');
        }

        // mode assign (default)
        if (!$position_id) {
            $this->session->set_flashdata('swal', ['type' => 'warning', 'message' => 'Pilih posisi terlebih dahulu']);
            return redirect('organization_settings/employee_position');
        }

        // cek exist
        $exist = $this->db->get_where('org_area_lvl_pstn_user', ['NRP' => $nrp])->row();

        if ($exist) {
            // UPDATE (kolom sesuai skema kamu: area_lvl_pstn_id)
            $ok = $this->db->where('NRP', $nrp)
                ->update('org_area_lvl_pstn_user', [
                    'area_lvl_pstn_id' => $position_id,
                ]);
        } else {
            // INSERT
            $ok = $this->db->insert('org_area_lvl_pstn_user', [
                'NRP'               => $nrp,
                'area_lvl_pstn_id'  => $position_id,
            ]);
        }

        $this->session->set_flashdata('swal', [
            'type' => $ok ? 'success' : 'error',
            'message' => $ok ? 'Posisi berhasil disimpan' : 'Gagal menyimpan posisi'
        ]);
        return redirect('organization_settings/employee_position');
    }
}
