<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_behavior_questionnaire_2024 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->load->helper(['conversion', 'extract_spreadsheet']);
        $culture_fit = extract_spreadsheet("./uploads/imports_admin/Behaviour Questionnaire 2024 import.xlsx") ?? [];
        $culture_fit = array_filter($culture_fit[0], fn($cf_i, $i_cf) => $i_cf >= 2 && $cf_i[1], ARRAY_FILTER_USE_BOTH);
        $data_inserts = [];
        foreach ($culture_fit as $i_cf => $cf_i) {
            $data_inserts[] = [
                'performance_review_reference' => $cf_i[0],
                'employee_id' => $cf_i[1],
                'employee' => $cf_i[2],
                'NRP' => $cf_i[3],
                'level' => $cf_i[4],
                'jabatan' => $cf_i[5],
                'layer' => $cf_i[6],
                'year' => $cf_i[7],
                'manager' => $cf_i[8],
                'NRP_manager' => $cf_i[9],
                'division' => $cf_i[10],
                'work_location' => $cf_i[11],
                'nilai_behaviour' => round($cf_i[12], 2),
            ];
        }
        if ($data_inserts) echo $this->db->insert_batch('culture_fit', $data_inserts);
    }
}
