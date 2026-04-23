<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class M_user extends CI_Model
{
    protected $table = 'rml_sso_la.users';

    protected $excelHeaderMap = [
        'Pers.No.' => 'NRP',
        'Full Name' => 'FullName',
        'Start Date' => 'StartDate',
        'End Date' => 'EndDate',
        'Act.' => 'Act',
        'Action Type' => 'ActionType',
        'ActR' => 'ActR',
        'Reason for Action' => 'ReasonForAction',
        'EEGrp' => 'EEGrp',
        'Employee Group' => 'EmployeeGroup',
        'ESgrp' => 'ESgrp',
        'Employee Subgroup' => 'EmployeeSubgroup',
        'PSubarea' => 'PSubarea',
        'Personnel Subarea' => 'PersonnelSubarea',
        'PArea' => 'PArea',
        'Payroll Area' => 'PayrollArea',
        'Org.unit' => 'OrgUnitCode',
        'Organizational Unit' => 'OrgUnitName',
        'Position' => 'PositionCode',
        'Position_2' => 'PositionName',
        'Gender' => 'GenderCode',
        'Gender_2' => 'Gender',
        'Birth date' => 'BirthDate',
        'Birthplace' => 'BirthPlace',
        'MarSt' => 'MarSt',
        'Marital Status Key' => 'MaritalStatus',
        'Rel' => 'RelCode',
        'Religious Denomination Key' => 'Religion',
        'Street and House Number' => 'Address',
        'City' => 'City',
        'District' => 'District',
        'Postal code' => 'PostalCode',
        'Bank Keys' => 'BankKey',
        'Payee' => 'PayeeName',
        'Bank Account' => 'BankAccount',
        'Membr' => 'Membr',
        'Type of Family Record' => 'FamilyType',
        'Full Name_2' => 'FamilyName',
        'CT' => 'CT',
        'Contract Type' => 'ContractType',
        'Cost Ctr' => 'CostCenter',
        'Work Schedule Rule' => 'WorkSchedule',
        'TR ID no' => 'TRID',
        'DT' => 'DateTypeCode',
        'Date type' => 'DateTypeDesc',
        'Date' => 'DateValue',
        'Type' => 'CommTypeCode',
        'Communication Type' => 'CommTypeDesc',
        'System ID' => 'SystemID',
        'Taxid' => 'TaxID',
        'TD' => 'TD',
        'Married for Tax Purposes' => 'MarriedForTax',
        'Spouse Benefit' => 'SpouseBenefit',
        'RDATE' => 'RDATE',
        'JAM ID' => 'JAMID',
        'Martial St' => 'MartialSt',
        'Terminate BPJS Pension Contrib' => 'TerminateBPJS',
        'BPJS ID' => 'BPJSID',
        'Number of Dependents' => 'Dependents',
        'Benefit Class for BPJS' => 'BPJSClass',
        'Start Date_2' => 'EduStartDate',
        'End Date_2' => 'EduEndDate',
        'Educational establishment' => 'Institute',
        'Institute/location' => 'InstituteLocation',
        'Education/training' => 'Education',
        'Dur.' => 'Duration',
        'Time/Measurement Unit' => 'DurationUnit',
        'Branch of study' => 'BranchOfStudy',
        'Final Grade' => 'FinalGrade',
        'E-mail' => 'Email',
        'ID Number' => 'IDNumber',
        'Type of identification (IC typ' => 'IDType',
    ];

    protected $editableColumns = [
        'NRP',
        'password',
        'pwd_ver',
        'password_updated_at',
        'FullName',
        'StartDate',
        'EndDate',
        'Act',
        'ActionType',
        'ActR',
        'ReasonForAction',
        'EEGrp',
        'EmployeeGroup',
        'ESgrp',
        'EmployeeSubgroup',
        'PSubarea',
        'PersonnelSubarea',
        'PArea',
        'PayrollArea',
        'OrgUnitCode',
        'OrgUnitName',
        'PositionCode',
        'PositionName',
        'GenderCode',
        'Gender',
        'BirthDate',
        'BirthPlace',
        'MarSt',
        'MaritalStatus',
        'RelCode',
        'Religion',
        'Address',
        'City',
        'District',
        'PostalCode',
        'BankKey',
        'PayeeName',
        'BankAccount',
        'Membr',
        'FamilyType',
        'FamilyName',
        'CT',
        'ContractType',
        'CostCenter',
        'WorkSchedule',
        'TRID',
        'DateTypeCode',
        'DateTypeDesc',
        'DateValue',
        'CommTypeCode',
        'CommTypeDesc',
        'SystemID',
        'TaxID',
        'TD',
        'MarriedForTax',
        'SpouseBenefit',
        'RDATE',
        'JAMID',
        'MartialSt',
        'TerminateBPJS',
        'BPJSID',
        'Dependents',
        'BPJSClass',
        'EduStartDate',
        'EduEndDate',
        'Institute',
        'InstituteLocation',
        'Education',
        'Duration',
        'DurationUnit',
        'BranchOfStudy',
        'FinalGrade',
        'Email',
        'IDNumber',
        'IDType',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function get_simple()
    {
        return $this->db
            ->select('id, NRP, FullName, BirthDate, password')
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result_array();
    }

    public function get_all()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result_array();
    }

    public function emptyStringToNull($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'emptyStringToNull'], $data);
        }

        return $data === '' ? null : $data;
    }

    protected function normalizeDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $value = trim((string)$value);

        $formats = ['Y-m-d', 'n/j/Y', 'm/d/Y', 'd/m/Y', 'd-m-Y', 'm-d-Y'];
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt && $dt->format($format) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        $strtotime = strtotime($value);
        if ($strtotime !== false) {
            return date('Y-m-d', $strtotime);
        }

        return null;
    }

    protected function birthDateToDefaultPassword($birthDate)
    {
        $normalized = $this->normalizeDate($birthDate);
        if (!$normalized) {
            return null;
        }

        return date('dmY', strtotime($normalized));
    }

    protected function normalizeHeader($headers)
    {
        $result = [];
        $counter = [];

        foreach ($headers as $header) {
            $header = trim((string)$header);

            if (!isset($counter[$header])) {
                $counter[$header] = 0;
                $result[] = $header;
            } else {
                $counter[$header]++;
                $result[] = $header . '_' . ($counter[$header] + 1);
            }
        }

        return $result;
    }

    protected function buildRowFromExcel($assoc)
    {
        $data = [];

        foreach ($this->excelHeaderMap as $excelHeader => $dbField) {
            $data[$dbField] = array_key_exists($excelHeader, $assoc) ? $assoc[$excelHeader] : null;
        }

        $dateFields = [
            'StartDate',
            'EndDate',
            'BirthDate',
            'DateValue',
            'RDATE',
            'EduStartDate',
            'EduEndDate',
            'password_updated_at'
        ];

        foreach ($dateFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeDate($data[$field]);
            }
        }

        return $this->emptyStringToNull($data);
    }

    public function import_excel($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);

            if (empty($rows) || count($rows) < 2) {
                return [
                    'success' => false,
                    'message' => 'File excel kosong atau format tidak valid'
                ];
            }

            $headers = $this->normalizeHeader($rows[0]);
            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $this->db->trans_begin();

            foreach (array_slice($rows, 1) as $row) {
                $assoc = [];
                foreach ($headers as $i => $header) {
                    $assoc[$header] = isset($row[$i]) ? trim((string)$row[$i]) : null;
                }

                $nrp = isset($assoc['Pers.No.']) ? trim((string)$assoc['Pers.No.']) : null;
                if (!$nrp) {
                    $skipped++;
                    continue;
                }

                $data = $this->buildRowFromExcel($assoc);
                $data['NRP'] = $nrp;

                $existing = $this->db
                    ->where('NRP', $nrp)
                    ->get($this->table)
                    ->row_array();

                if ($existing) {
                    if (empty($existing['password'])) {
                        $defaultPassword = $this->birthDateToDefaultPassword($data['BirthDate'] ?? null);
                        if ($defaultPassword) {
                            $data['password'] = password_hash($defaultPassword, PASSWORD_DEFAULT);
                            $data['password_updated_at'] = date('Y-m-d H:i:s');
                        } else {
                            unset($data['password']);
                        }
                    } else {
                        unset($data['password']);
                    }

                    $this->db->where('NRP', $nrp)->update($this->table, $data);
                    $updated++;
                } else {
                    $defaultPassword = $this->birthDateToDefaultPassword($data['BirthDate'] ?? null);
                    $data['password'] = $defaultPassword ? password_hash($defaultPassword, PASSWORD_DEFAULT) : null;
                    $data['password_updated_at'] = $defaultPassword ? date('Y-m-d H:i:s') : null;
                    $data['created_at'] = date('Y-m-d H:i:s');

                    $this->db->insert($this->table, $data);
                    $inserted++;
                }
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'Gagal import excel'
                ];
            }

            $this->db->trans_commit();

            return [
                'success' => true,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function submit($payload)
    {
        $updates = $this->emptyStringToNull($payload['updates'] ?? []);
        if (empty($updates)) {
            return false;
        }

        $success = false;

        $this->db->trans_begin();

        foreach ($updates as $row) {
            if (!isset($row['id']) || !is_numeric($row['id'])) {
                continue;
            }

            $id = (int)$row['id'];

            $existing = $this->db
                ->where('id', $id)
                ->get($this->table)
                ->row_array();

            if (!$existing) {
                continue;
            }

            $updateData = [];

            foreach ($this->editableColumns as $col) {
                if (array_key_exists($col, $row)) {
                    $updateData[$col] = $row[$col];
                }
            }

            $dateFields = [
                'StartDate',
                'EndDate',
                'BirthDate',
                'DateValue',
                'RDATE',
                'EduStartDate',
                'EduEndDate',
                'password_updated_at'
            ];

            foreach ($dateFields as $field) {
                if (array_key_exists($field, $updateData)) {
                    $updateData[$field] = $this->normalizeDate($updateData[$field]);
                }
            }

            if (array_key_exists('password', $updateData)) {
                $rawPassword = trim((string)$updateData['password']);

                if ($rawPassword === '' || $rawPassword === '********') {
                    unset($updateData['password']);
                } else {
                    $updateData['password'] = password_hash($rawPassword, PASSWORD_DEFAULT);
                    $updateData['password_updated_at'] = date('Y-m-d H:i:s');
                }
            }

            if (!empty($updateData)) {
                $this->db->where('id', $id)->update($this->table, $updateData);
                $success = true;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        return $success;
    }
}
