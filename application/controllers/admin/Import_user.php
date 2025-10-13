<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_user extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        $data['content'] = "admin/import_user";
        $this->load->view('templates/header_footer', $data);
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

    public function submit()
    {
        $success = false;
        $important_columns = ['NRP', 'FullName', 'BirthDate', 'PSubarea', 'OrgUnitName', 'PositionName', 'ActionType'];
        $payload = json_decode($this->input->post('json_data'), true);
        $table = 'rml_sso_la.users';
        $newAutoData = [];
        $oldAutoData = [];
        $newData = [];
        $oldData = [];

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
                // $this->db->update_batch($table, $updateData, 'id');
                $success = true;
            }
        }

        // DELETES
        if (!empty($deletes)) {
            // $this->db->where_in('id', $deletes)->delete($table);
            $success = true;
        }

        // CREATES
        if (!empty($creates)) {
            $creates = array_filter($creates, function ($row) use ($deletes) {
                return !(isset($row['id']) && in_array($row['id'], $deletes));
            });

            foreach ($creates as &$create) {
                foreach ($create as $key => $value) {
                    // Lewati jika kosong atau bukan string
                    if (empty($value) || !is_string($value)) continue;

                    // Cek format m/d/Y atau mm/dd/yyyy
                    if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                        $date = DateTime::createFromFormat('n/j/Y', $value);
                        if ($date) {
                            $create[$key] = $date->format('Y-m-d');
                        }
                    }
                }
            }

            $nrps = array_column($this->db->get($table)->result_array(), null, 'NRP');
            $updateData = [];
            $createData = [];
            $terminateData = [];
            $this->load->model('employee/m_employee', 'm_emp');
            $this->load->model('organization/m_position', 'm_pstn');
            $positions = $this->m_pstn->get_area_lvl_pstn();
            foreach ($creates as $row) {
                if (!$row['FullName']) {
                    continue;
                }
                if (isset($row['NRP'])) {
                    unset($row['id']);
                    // update
                    if (in_array($row['NRP'], array_keys($nrps))) {
                        if ($row['ActionType'] == 'Terminate') {
                            $terminateData[$row['NRP']] = $row;
                        } else {
                            $updateData[$row['NRP']] = $row;
                            $old = $nrps[$row['NRP']];
                            $new = $row;
                            if (
                                (
                                    $new['FullName'] != $old['FullName'] ||
                                    $new['BirthDate'] != $old['BirthDate'] ||
                                    $new['PSubarea'] != $old['PSubarea'] ||
                                    $new['EmployeeSubgroup'] != $old['EmployeeSubgroup'] ||
                                    $new['OrgUnitName'] != $old['OrgUnitName'] ||
                                    $new['PositionName'] != $old['PositionName']
                                ) && substr($row['PSubarea'], 0, 1) != 'N'
                            ) {
                                $difference = 0;
                                foreach ($important_columns as $i_ic => $ic_i) {
                                    $new[$ic_i . '_flag'] = false;
                                    $old[$ic_i . '_flag'] = false;
                                    if ($new[$ic_i] != $old[$ic_i] && $ic_i != 'ActionType') {
                                        $new[$ic_i . '_flag'] = true;
                                        $old[$ic_i . '_flag'] = true;
                                        $difference++;
                                    }
                                }
                                if (
                                    ($difference <= 2 && $new['FullName_flag'] && $new['BirthDate_flag']) ||
                                    ($difference <= 1 && ($new['FullName_flag'] || $new['BirthDate_flag']))
                                ) {
                                    // $newData[$row['NRP']] = $new;
                                    // $oldData[$row['NRP']] = $old;
                                    // $oldData[$row['NRP']]['difference'] = 'difference: ' . $difference;
                                } else {
                                    // $position = array_filter(
                                    //     $positions,
                                    //     fn($pos_i, $i_pos) =>
                                    //     $pos_i['oa_name'] == $new['PSubarea'] &&
                                    //         (
                                    //             $pos_i['oal_name'] == $new['EmployeeSubgroup'] ||
                                    //             (
                                    //                 $pos_i['oal_name'] == 'Officer Site/GroupLead' && strtolower($new['EmployeeSubgroup']) == strtolower('Officer HO/GroupLead')
                                    //             )
                                    //         ) &&
                                    //         stripos($new['PositionName'], $pos_i['name']) !== false,
                                    //     ARRAY_FILTER_USE_BOTH
                                    // );

                                    $position = array_filter(
                                        $positions,
                                        function ($pos_i, $i_pos) use ($new) {
                                            // match area/subgroup seperti semula
                                            if ($pos_i['oa_name'] != $new['PSubarea']) return false;
                                            if (
                                                !(
                                                    $pos_i['oal_name'] == $new['EmployeeSubgroup'] ||
                                                    ($pos_i['oal_name'] == 'Officer Site/GroupLead' && strcasecmp($new['EmployeeSubgroup'], 'Officer HO/GroupLead') === 0)
                                                )
                                            ) return false;

                                            // ganti stripos dengan smart_match
                                            return $this->smart_match($new['PositionName'], $pos_i['name']);
                                        },
                                        ARRAY_FILTER_USE_BOTH
                                    );
                                    if (count($position) == 1) {
                                        $position = array_shift($position);
                                        $employee = $this->m_emp->get_employee($new['NRP'], 'users.NRP', false);
                                        if ($employee['oalp_id'] != $position['id']) {
                                            $newAutoData[$row['NRP']] = $new;
                                            $newAutoData[$row['NRP']]['found_position'] = $position['oa_name'] . ' | ' . $position['oal_name'] . ' | |' . $position['name'];
                                            $oldAutoData[$row['NRP']] = $old;
                                        }
                                    } else {
                                        $newData[$row['NRP']] = $new;
                                        $oldData[$row['NRP']] = $old;
                                    }
                                }
                            }
                            if ($new['BirthDate'] != $old['BirthDate'] && !$old['pwd_ver']) {
                                $newDate = date("dmY", strtotime($new['BirthDate']));
                                $updateData[$row['NRP']]['password'] = password_hash($newDate, PASSWORD_DEFAULT);
                            }
                        }
                    }
                    // insert
                    else {
                        $newDate = date("dmY", strtotime($row['BirthDate']));
                        $row['password'] = password_hash($newDate, PASSWORD_DEFAULT);
                        $createData[] = $row;
                    }
                }
            }

            if ($this->input->post('action') == 'submit') {
                if (!empty($terminateData)) {
                    $nrps = array_column($terminateData, 'NRP');

                    $this->db->where_in('NRP', $nrps);
                    $success = $this->db->delete('org_area_lvl_pstn_user');
                }

                if (!empty($updateData)) {
                    $success = $this->db->update_batch($table, $updateData, 'NRP');
                }

                if (!empty($createData)) {
                    $this->db->insert_batch($table, $createData);
                }

                flash_swal('error', 'Data Submission Failed');
                if ($success) {
                    flash_swal('success', 'Data Submitted Successfully');
                }
            } else {
                $success = true;
                flash_swal('error', 'Data Compared Failed');
                if ($success) {
                    flash_swal('success', 'Data Compared Successfully');
                }
            }
        }
        $data['columns'] = $important_columns;
        $data['createData'] = $createData;
        $data['updateData'] = $updateData;
        $data['terminateData'] = $terminateData;
        $data['newAutoData'] = $newAutoData;
        $data['oldAutoData'] = $oldAutoData;
        $data['newData'] = $newData;
        $data['oldData'] = $oldData;
        $data['content'] = "admin/import_user_compare";
        $this->load->view('templates/header_footer', $data);
        // redirect("admin/import_user");
    }

    function enrich_area_lvl_pstn_rows(array $rows): array
    {
        return array_map(function ($row) {
            $full = isset($row['name']) ? trim($row['name']) : '';          // oalp.name full (mis. "RMHO-SECTION HEAD PROJECT COST CONTROL")
            $oa   = isset($row['oa_name']) ? trim($row['oa_name']) : '';     // dari join
            $oal  = isset($row['oal_name']) ? trim($row['oal_name']) : '';   // dari join

            // Ambil string setelah tanda '-' pertama (bagian level+posisi)
            $afterDash = $full;
            if (strpos($full, '-') !== false) {
                $afterDash = trim(substr($full, strpos($full, '-') + 1));
            }

            // Hapus prefix level jabatan (oal_name) dari $afterDash (case-insensitive, toleran spasi)
            $role = $afterDash;
            if ($oal !== '') {
                // Normalisasi spasi beruntun
                $norm = fn($s) => preg_replace('/\s+/', ' ', trim($s));
                $oalN = $norm($oal);
                $aftN = $norm($afterDash);

                // Jika $aftN diawali oalN, potong; contoh: "SECTION HEAD PROJECT COST CONTROL"
                if (stripos($aftN, $oalN . ' ') === 0 || strcasecmp($aftN, $oalN) === 0) {
                    $role = trim(substr($aftN, strlen($oalN)));
                } else {
                    // fallback regex lebih toleran (spasi/tanda baca)
                    $pattern = '/^' . preg_quote($oalN, '/') . '\b[\s\-_:]*?/i';
                    $role = trim(preg_replace($pattern, '', $afterDash));
                }
            }

            // Lowercase sesuai contoh yang kamu mau
            $row['oa_name']   = $oa;                          // biarkan apa adanya (kode site biasanya uppercase)
            $row['oal_name']  = strtolower($oal);
            $row['oalp_name'] = strtolower($role);

            return $row;
        }, $rows);
    }

    // 2) Normalisasi: uppercase, expand alias, buang tanda baca, rapikan spasi
    function normalize_title(string $s): string
    {
        $TITLE_ALIASES = [
            'PROD.' => 'PRODUCTION',
            'PROD'  => 'PRODUCTION',
            'OPR.' => 'OPERATION',
            'OPR'  => 'OPERATION',
            'DEV.' => 'DEVELOPMENT',
            'DEV'  => 'DEVELOPMENT',
            'EQP.'  => 'EQUIPMENT',
            'EQP'   => 'EQUIPMENT',
        ];
        // expand alias per kata (pakai boundary biar nggak nyasar)
        foreach ($TITLE_ALIASES as $short => $full) {
            $pattern = '/\b' . preg_quote($short, '/') . '\b/i';
            $s = preg_replace($pattern, $full, $s);
        }
        // ganti non-alnum jadi spasi
        $s = preg_replace('/[^A-Z0-9]+/i', ' ', $s);
        // trim + collapse spaces + uppercase
        $s = strtoupper(trim(preg_replace('/\s+/', ' ', $s)));
        return $s;
    }

    // 3) Ubah jadi token unik
    function tokens(string $s): array
    {
        $s = $this->normalize_title($s);
        if ($s === '') return [];
        $arr = explode(' ', $s);
        return array_values(array_unique(array_filter($arr)));
    }

    // 4) Smart match: cek apakah nama posisi referensi “terkandung” di judul target
    //    via subset token atau overlap minimum (mis. 70%)
    function smart_match(string $haystackTitle, string $needleTitle, float $minOverlap = 0.7): bool
    {
        $h = $this->tokens($haystackTitle);   // contoh: "PLANT PRODUCTION EQUIPMENT TRACK"
        $n = $this->tokens($needleTitle);     // contoh: "PROD. EQP. TRACK" -> jadi "PRODUCTION EQUIPMENT TRACK"

        if (!$n || !$h) return false;

        // subset: semua token needle ada di haystack
        $miss = array_diff($n, $h);
        if (count($miss) === 0) return true;

        // atau, pakai rasio overlap (berguna kalau urutan/stopword beda)
        $inter = array_intersect($n, $h);
        $overlap = count($inter) / count($n);
        return $overlap >= $minOverlap;
    }
}
