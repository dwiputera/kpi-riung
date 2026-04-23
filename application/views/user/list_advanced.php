<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">User Management - Advanced</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Advanced User List</h3>
            </div>

            <form action="<?= base_url('user/submit') ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
                <input type="hidden" name="mode" value="advanced">

                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-warning" id="btn-enable-edit">
                            <i class="fas fa-edit"></i> Edit Mode
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-disable-edit" style="display:none;">
                            <i class="fas fa-lock"></i> Lock Mode
                        </button>

                        <a href="<?= base_url('user') ?>" class="btn btn-default ml-2">
                            <i class="fas fa-arrow-left"></i> Back to Simple List
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered table-striped table-sm datatable-filter-column">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NRP</th>
                                    <th>PASSWORD</th>
                                    <th>PWD VER</th>
                                    <th>PASSWORD UPDATED AT</th>
                                    <th>FULL NAME</th>
                                    <th>START DATE</th>
                                    <th>END DATE</th>
                                    <th>ACT</th>
                                    <th>ACTION TYPE</th>
                                    <th>ACTR</th>
                                    <th>REASON FOR ACTION</th>
                                    <th>EEGRP</th>
                                    <th>EMPLOYEE GROUP</th>
                                    <th>ESGRP</th>
                                    <th>EMPLOYEE SUBGROUP</th>
                                    <th>PSUBAREA</th>
                                    <th>PERSONNEL SUBAREA</th>
                                    <th>PAREA</th>
                                    <th>PAYROLL AREA</th>
                                    <th>ORG UNIT CODE</th>
                                    <th>ORG UNIT NAME</th>
                                    <th>POSITION CODE</th>
                                    <th>POSITION NAME</th>
                                    <th>GENDER CODE</th>
                                    <th>GENDER</th>
                                    <th>BIRTH DATE</th>
                                    <th>BIRTH PLACE</th>
                                    <th>MARST</th>
                                    <th>MARITAL STATUS</th>
                                    <th>RELCODE</th>
                                    <th>RELIGION</th>
                                    <th>ADDRESS</th>
                                    <th>CITY</th>
                                    <th>DISTRICT</th>
                                    <th>POSTAL CODE</th>
                                    <th>BANK KEY</th>
                                    <th>PAYEE NAME</th>
                                    <th>BANK ACCOUNT</th>
                                    <th>MEMBR</th>
                                    <th>FAMILY TYPE</th>
                                    <th>FAMILY NAME</th>
                                    <th>CT</th>
                                    <th>CONTRACT TYPE</th>
                                    <th>COST CENTER</th>
                                    <th>WORK SCHEDULE</th>
                                    <th>TRID</th>
                                    <th>DATE TYPE CODE</th>
                                    <th>DATE TYPE DESC</th>
                                    <th>DATE VALUE</th>
                                    <th>COMM TYPE CODE</th>
                                    <th>COMM TYPE DESC</th>
                                    <th>SYSTEM ID</th>
                                    <th>TAX ID</th>
                                    <th>TD</th>
                                    <th>MARRIED FOR TAX</th>
                                    <th>SPOUSE BENEFIT</th>
                                    <th>RDATE</th>
                                    <th>JAMID</th>
                                    <th>MARTIAL ST</th>
                                    <th>TERMINATE BPJS</th>
                                    <th>BPJSID</th>
                                    <th>DEPENDENTS</th>
                                    <th>BPJS CLASS</th>
                                    <th>EDU START DATE</th>
                                    <th>EDU END DATE</th>
                                    <th>INSTITUTE</th>
                                    <th>INSTITUTE LOCATION</th>
                                    <th>EDUCATION</th>
                                    <th>DURATION</th>
                                    <th>DURATION UNIT</th>
                                    <th>BRANCH OF STUDY</th>
                                    <th>FINAL GRADE</th>
                                    <th>EMAIL</th>
                                    <th>ID NUMBER</th>
                                    <th>ID TYPE</th>
                                    <th>CREATED AT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u) : ?>
                                    <tr data-id="<?= $u['id'] ?>">
                                        <td><?= $u['id'] ?></td>
                                        <td data-name="NRP"><?= htmlspecialchars($u['NRP'] ?? '') ?></td>
                                        <td data-name="password"><?= !empty($u['password']) ? '********' : '' ?></td>
                                        <td data-name="pwd_ver"><?= htmlspecialchars($u['pwd_ver'] ?? '') ?></td>
                                        <td data-name="password_updated_at"><?= htmlspecialchars($u['password_updated_at'] ?? '') ?></td>
                                        <td data-name="FullName"><?= htmlspecialchars($u['FullName'] ?? '') ?></td>
                                        <td data-name="StartDate"><?= htmlspecialchars($u['StartDate'] ?? '') ?></td>
                                        <td data-name="EndDate"><?= htmlspecialchars($u['EndDate'] ?? '') ?></td>
                                        <td data-name="Act"><?= htmlspecialchars($u['Act'] ?? '') ?></td>
                                        <td data-name="ActionType"><?= htmlspecialchars($u['ActionType'] ?? '') ?></td>
                                        <td data-name="ActR"><?= htmlspecialchars($u['ActR'] ?? '') ?></td>
                                        <td data-name="ReasonForAction"><?= htmlspecialchars($u['ReasonForAction'] ?? '') ?></td>
                                        <td data-name="EEGrp"><?= htmlspecialchars($u['EEGrp'] ?? '') ?></td>
                                        <td data-name="EmployeeGroup"><?= htmlspecialchars($u['EmployeeGroup'] ?? '') ?></td>
                                        <td data-name="ESgrp"><?= htmlspecialchars($u['ESgrp'] ?? '') ?></td>
                                        <td data-name="EmployeeSubgroup"><?= htmlspecialchars($u['EmployeeSubgroup'] ?? '') ?></td>
                                        <td data-name="PSubarea"><?= htmlspecialchars($u['PSubarea'] ?? '') ?></td>
                                        <td data-name="PersonnelSubarea"><?= htmlspecialchars($u['PersonnelSubarea'] ?? '') ?></td>
                                        <td data-name="PArea"><?= htmlspecialchars($u['PArea'] ?? '') ?></td>
                                        <td data-name="PayrollArea"><?= htmlspecialchars($u['PayrollArea'] ?? '') ?></td>
                                        <td data-name="OrgUnitCode"><?= htmlspecialchars($u['OrgUnitCode'] ?? '') ?></td>
                                        <td data-name="OrgUnitName"><?= htmlspecialchars($u['OrgUnitName'] ?? '') ?></td>
                                        <td data-name="PositionCode"><?= htmlspecialchars($u['PositionCode'] ?? '') ?></td>
                                        <td data-name="PositionName"><?= htmlspecialchars($u['PositionName'] ?? '') ?></td>
                                        <td data-name="GenderCode"><?= htmlspecialchars($u['GenderCode'] ?? '') ?></td>
                                        <td data-name="Gender"><?= htmlspecialchars($u['Gender'] ?? '') ?></td>
                                        <td data-name="BirthDate"><?= htmlspecialchars($u['BirthDate'] ?? '') ?></td>
                                        <td data-name="BirthPlace"><?= htmlspecialchars($u['BirthPlace'] ?? '') ?></td>
                                        <td data-name="MarSt"><?= htmlspecialchars($u['MarSt'] ?? '') ?></td>
                                        <td data-name="MaritalStatus"><?= htmlspecialchars($u['MaritalStatus'] ?? '') ?></td>
                                        <td data-name="RelCode"><?= htmlspecialchars($u['RelCode'] ?? '') ?></td>
                                        <td data-name="Religion"><?= htmlspecialchars($u['Religion'] ?? '') ?></td>
                                        <td data-name="Address"><?= htmlspecialchars($u['Address'] ?? '') ?></td>
                                        <td data-name="City"><?= htmlspecialchars($u['City'] ?? '') ?></td>
                                        <td data-name="District"><?= htmlspecialchars($u['District'] ?? '') ?></td>
                                        <td data-name="PostalCode"><?= htmlspecialchars($u['PostalCode'] ?? '') ?></td>
                                        <td data-name="BankKey"><?= htmlspecialchars($u['BankKey'] ?? '') ?></td>
                                        <td data-name="PayeeName"><?= htmlspecialchars($u['PayeeName'] ?? '') ?></td>
                                        <td data-name="BankAccount"><?= htmlspecialchars($u['BankAccount'] ?? '') ?></td>
                                        <td data-name="Membr"><?= htmlspecialchars($u['Membr'] ?? '') ?></td>
                                        <td data-name="FamilyType"><?= htmlspecialchars($u['FamilyType'] ?? '') ?></td>
                                        <td data-name="FamilyName"><?= htmlspecialchars($u['FamilyName'] ?? '') ?></td>
                                        <td data-name="CT"><?= htmlspecialchars($u['CT'] ?? '') ?></td>
                                        <td data-name="ContractType"><?= htmlspecialchars($u['ContractType'] ?? '') ?></td>
                                        <td data-name="CostCenter"><?= htmlspecialchars($u['CostCenter'] ?? '') ?></td>
                                        <td data-name="WorkSchedule"><?= htmlspecialchars($u['WorkSchedule'] ?? '') ?></td>
                                        <td data-name="TRID"><?= htmlspecialchars($u['TRID'] ?? '') ?></td>
                                        <td data-name="DateTypeCode"><?= htmlspecialchars($u['DateTypeCode'] ?? '') ?></td>
                                        <td data-name="DateTypeDesc"><?= htmlspecialchars($u['DateTypeDesc'] ?? '') ?></td>
                                        <td data-name="DateValue"><?= htmlspecialchars($u['DateValue'] ?? '') ?></td>
                                        <td data-name="CommTypeCode"><?= htmlspecialchars($u['CommTypeCode'] ?? '') ?></td>
                                        <td data-name="CommTypeDesc"><?= htmlspecialchars($u['CommTypeDesc'] ?? '') ?></td>
                                        <td data-name="SystemID"><?= htmlspecialchars($u['SystemID'] ?? '') ?></td>
                                        <td data-name="TaxID"><?= htmlspecialchars($u['TaxID'] ?? '') ?></td>
                                        <td data-name="TD"><?= htmlspecialchars($u['TD'] ?? '') ?></td>
                                        <td data-name="MarriedForTax"><?= htmlspecialchars($u['MarriedForTax'] ?? '') ?></td>
                                        <td data-name="SpouseBenefit"><?= htmlspecialchars($u['SpouseBenefit'] ?? '') ?></td>
                                        <td data-name="RDATE"><?= htmlspecialchars($u['RDATE'] ?? '') ?></td>
                                        <td data-name="JAMID"><?= htmlspecialchars($u['JAMID'] ?? '') ?></td>
                                        <td data-name="MartialSt"><?= htmlspecialchars($u['MartialSt'] ?? '') ?></td>
                                        <td data-name="TerminateBPJS"><?= htmlspecialchars($u['TerminateBPJS'] ?? '') ?></td>
                                        <td data-name="BPJSID"><?= htmlspecialchars($u['BPJSID'] ?? '') ?></td>
                                        <td data-name="Dependents"><?= htmlspecialchars($u['Dependents'] ?? '') ?></td>
                                        <td data-name="BPJSClass"><?= htmlspecialchars($u['BPJSClass'] ?? '') ?></td>
                                        <td data-name="EduStartDate"><?= htmlspecialchars($u['EduStartDate'] ?? '') ?></td>
                                        <td data-name="EduEndDate"><?= htmlspecialchars($u['EduEndDate'] ?? '') ?></td>
                                        <td data-name="Institute"><?= htmlspecialchars($u['Institute'] ?? '') ?></td>
                                        <td data-name="InstituteLocation"><?= htmlspecialchars($u['InstituteLocation'] ?? '') ?></td>
                                        <td data-name="Education"><?= htmlspecialchars($u['Education'] ?? '') ?></td>
                                        <td data-name="Duration"><?= htmlspecialchars($u['Duration'] ?? '') ?></td>
                                        <td data-name="DurationUnit"><?= htmlspecialchars($u['DurationUnit'] ?? '') ?></td>
                                        <td data-name="BranchOfStudy"><?= htmlspecialchars($u['BranchOfStudy'] ?? '') ?></td>
                                        <td data-name="FinalGrade"><?= htmlspecialchars($u['FinalGrade'] ?? '') ?></td>
                                        <td data-name="Email"><?= htmlspecialchars($u['Email'] ?? '') ?></td>
                                        <td data-name="IDNumber"><?= htmlspecialchars($u['IDNumber'] ?? '') ?></td>
                                        <td data-name="IDType"><?= htmlspecialchars($u['IDType'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane"></i> Submit Update
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    const editableFields = [
        'NRP', 'password', 'pwd_ver', 'password_updated_at', 'FullName', 'StartDate', 'EndDate', 'Act', 'ActionType',
        'ActR', 'ReasonForAction', 'EEGrp', 'EmployeeGroup', 'ESgrp', 'EmployeeSubgroup', 'PSubarea',
        'PersonnelSubarea', 'PArea', 'PayrollArea', 'OrgUnitCode', 'OrgUnitName', 'PositionCode', 'PositionName',
        'GenderCode', 'Gender', 'BirthDate', 'BirthPlace', 'MarSt', 'MaritalStatus', 'RelCode', 'Religion',
        'Address', 'City', 'District', 'PostalCode', 'BankKey', 'PayeeName', 'BankAccount', 'Membr',
        'FamilyType', 'FamilyName', 'CT', 'ContractType', 'CostCenter', 'WorkSchedule', 'TRID',
        'DateTypeCode', 'DateTypeDesc', 'DateValue', 'CommTypeCode', 'CommTypeDesc', 'SystemID', 'TaxID',
        'TD', 'MarriedForTax', 'SpouseBenefit', 'RDATE', 'JAMID', 'MartialSt', 'TerminateBPJS', 'BPJSID',
        'Dependents', 'BPJSClass', 'EduStartDate', 'EduEndDate', 'Institute', 'InstituteLocation', 'Education',
        'Duration', 'DurationUnit', 'BranchOfStudy', 'FinalGrade', 'Email', 'IDNumber', 'IDType'
    ];

    let isEditMode = false;

    function applyEditModeToVisibleRows() {
        $('#datatable tbody tr').each(function() {
            $(this).find('td[data-name]').each(function() {
                const field = $(this).data('name');

                if (editableFields.includes(field)) {
                    $(this).attr('contenteditable', isEditMode ? 'true' : 'false');
                    $(this).toggleClass('bg-light', isEditMode);
                }
            });
        });

        if (isEditMode) {
            $('#btn-enable-edit').hide();
            $('#btn-disable-edit').show();
        } else {
            $('#btn-enable-edit').show();
            $('#btn-disable-edit').hide();
        }
    }

    function setEditMode(enabled) {
        isEditMode = enabled;
        applyEditModeToVisibleRows();
    }

    function collectPayload() {
        const updates = [];

        $('#datatable tbody tr').each(function() {
            const $tr = $(this);
            const row = {
                id: $tr.data('id')
            };

            $tr.find('td[data-name]').each(function() {
                const field = $(this).data('name');
                row[field] = $(this).text().trim();
            });

            updates.push(row);
        });

        return {
            updates: updates,
            creates: [],
            deletes: []
        };
    }

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
        setEditMode(false);

        $('#btn-enable-edit').on('click', function() {
            setEditMode(true);
        });

        $('#btn-disable-edit').on('click', function() {
            setEditMode(false);
        });

        // Penting: apply lagi setiap redraw DataTable
        $('#datatable').on('draw.dt', function() {
            applyEditModeToVisibleRows();
        });

        $('#data-form').on('submit', function() {
            $('#json_data').val(JSON.stringify(collectPayload()));
        });
    });
</script>