<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Import User</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Behavior Questionnaire</h3>
            </div>
            <form action="<?= base_url() ?>admin/import_user/submit" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
                <!-- /.card-header -->
                <div class="card-body">
                    <?php
                    // Daftar kolom sesuai permintaan (urutannya dipakai untuk header & body)
                    $columns = [
                        'id',
                        'NRP',
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
                        'created_at'
                    ];
                    ?>
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>
                                <?php foreach ($columns as $col): ?>
                                    <?php
                                    // kolom yang tidak ingin diedit langsung (id biasanya non-editable)
                                    $nonEditable = in_array($col, ['id']) ? '1' : '0';
                                    ?>
                                    <th data-column="<?= $col ?>" data-noneditable="<?= $nonEditable ?>">
                                        <?= strtoupper($col) ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-2">
                            <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                        <div class="col-lg-2">
                            <button type="button" class="w-100 btn btn-danger" onclick="deleteSelectedRows()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="col-lg-3 d-flex">
                            <input type="number" class="form-control w-50" id="row_number_add" name="row_number_add" value="1">
                            <button type="button" class="w-50 btn btn-success" onclick="createRow()">
                                <i class="fas fa-plus"></i> New
                            </button>
                        </div>
                        <div class="col-lg-2">
                            <button type="submit" class="w-100 btn btn-primary" name="action" value="compare">
                                <i class="fas fa-balance-scale"></i> Compare
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="submit" class="w-100 btn btn-info" name="action" value="submit">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    let deletedRows = [];

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        $('#select-all').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $('#data-form').on('submit', function() {
            let allRows = collectTableData();
            let payload = {
                updates: allRows.filter(r => !String(r.id).startsWith('new_')),
                deletes: deletedRows,
                creates: allRows.filter(r => String(r.id).startsWith('new_'))
            };
            $('#json_data').val(JSON.stringify(payload));
        });
    });

    function markRowDeleted(btn) {
        let row = $(btn).closest('tr');
        let id = row.data('id');
        let checkbox = row.find('.row-checkbox');

        if (deletedRows.includes(id)) {
            // ✅ Restore if already marked deleted
            deletedRows = deletedRows.filter(x => x !== id);
            row.removeClass('table-danger').css('opacity', '1');
            checkbox.prop('checked', false); // uncheck when restored
        } else {
            // ✅ Mark as deleted
            deletedRows.push(id);
            row.addClass('table-danger').css('opacity', '0.7');
            checkbox.prop('checked', true); // auto-check when deleted
        }
    }

    function deleteSelectedRows() {
        let table = $('#datatable').DataTable();

        // Loop melalui semua baris di DataTable, termasuk yang ada di halaman lain
        table.rows().every(function() {
            let row = $(this.node());
            let id = row.data('id');
            let isChecked = row.find('.row-checkbox').prop('checked');

            if (isChecked) {
                // Tandai baris yang dipilih untuk dihapus
                if (!deletedRows.includes(id)) {
                    deletedRows.push(id);
                }
                row.addClass('table-danger').css('opacity', '0.7');
            } else {
                // Jika baris tidak dipilih, pastikan untuk menghapus status 'deleted'
                if (deletedRows.includes(id)) {
                    deletedRows = deletedRows.filter(x => x !== id);
                    row.removeClass('table-danger').css('opacity', '1');
                }
            }
        });

        // Pastikan DataTable diupdate setelah penghapusan
        table.draw(false); // Redraw the table to ensure changes are applied across pages
    }

    // Ambil daftar kolom dari header (kecuali checkbox & NO)
    function getTableColumns() {
        let cols = [];
        $('#datatable thead th[data-column]').each(function() {
            const name = $(this).data('column');
            const nonEditable = $(this).data('noneditable') == '1';
            cols.push({
                name,
                nonEditable
            });
        });
        return cols;
    }

    function createRow() {
        showOverlayFull();
        const table = $('#datatable').DataTable();
        const row_number_add = parseInt($('#row_number_add').val() || '1', 10);
        const cols = getTableColumns();

        // 1) siapkan batch rows (array of arrays) TANPA draw per item
        const batchRows = [];
        const newIdBase = Date.now();

        for (let x = 0; x < row_number_add; x++) {
            const newId = 'new_' + newIdBase + '_' + x;
            const rowArray = [];

            // kolom 1: checkbox
            rowArray.push('<input type="checkbox" class="row-checkbox">');
            // kolom 2: NO
            rowArray.push('New');

            // kolom dinamis (pakai <span contenteditable> agar ringan saat render awal)
            for (let i = 0; i < cols.length; i++) {
                const c = cols[i];
                if (c.name === 'id') {
                    rowArray.push(newId);
                } else {
                    rowArray.push(`<span contenteditable="true" data-column="${c.name}"></span>`);
                }
            }

            batchRows.push(rowArray);
        }

        // 2) tambahkan SEMUA baris sekaligus + 1x draw
        const addedNodes = table.rows.add(batchRows).draw(false).nodes().to$();

        // 3) set atribut data-id + kelas ke semua baris baru
        //    sekaligus (pakai for langsung lebih cepat daripada each)
        for (let i = 0; i < addedNodes.length; i++) {
            const node = addedNodes[i];
            const idx = i; // urutan dalam batch
            $(node).attr('data-id', 'new_' + newIdBase + '_' + idx).addClass('table-success');
        }

        // 4) agar paste Excel tetap jalan (listener-nya di td[contenteditable])
        //    set contenteditable langsung ke <td> dinamis untuk baris-baris yang barusan ditambahkan.
        //    Hindari loop mahal per-td untuk seluruh tabel – cukup untuk batch yang baru.
        const $thead = $('#datatable thead');
        const ths = $thead.find('th');
        addedNodes.each(function() {
            const $tr = $(this);
            // mulai dari kolom index 2 (setara penomoran kamu)
            // gunakan indeks <th> untuk tahu mana yang nonEditable
            $tr.children('td').each(function(tdIdx) {
                if (tdIdx < 2) return; // skip checkbox+NO
                const $th = $(ths[tdIdx]);
                const col = $th.data('column');
                const nonEditable = $th.data('noneditable') == 1;
                if (col && !nonEditable) {
                    // contenteditable di <td> diperlukan agar paste (td[contenteditable]) tetap berfungsi
                    this.setAttribute('contenteditable', 'true');
                    this.setAttribute('data-column', col);
                } else if (col === 'id') {
                    this.removeAttribute('contenteditable');
                    this.setAttribute('data-column', 'id');
                }
            });
        });

        table.columns.adjust().draw(false);
        table.page('last').draw('page');
        hideOverlayFull();
    }

    function collectTableData() {
        const dt = $('#datatable').DataTable();
        const data = [];

        dt.rows().every(function() {
            const $row = $(this.node());
            data.push(collectRow($row));
        });

        // Tidak perlu scan ulang tbody: DataTables API sudah mencakup semua baris (termasuk yang off-page).
        return data;
    }

    // Kumpulkan data baris (dinamis)
    function collectRow(row) {
        const id = row.data('id');
        const rowData = {
            id: id
        };

        // ambil semua cell yang punya data-column (baik contenteditable atau bukan)
        row.find('td[data-column], span[data-column]').each(function() {
            const col = $(this).data('column');
            if (!col) return;

            // 'id' kita ambil dari atribut data-id baris agar konsisten
            if (col === 'id') {
                rowData['id'] = id;
                return;
            }

            // Jika elemen input/select, ambil val; jika contenteditable/text, ambil text
            if ($(this).is('input, select, textarea')) {
                rowData[col] = $(this).val();
            } else {
                rowData[col] = $(this).text();
            }
        });

        return rowData;
    }
</script>