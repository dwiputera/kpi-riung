<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Culture Fit <?= $year ?></h1>
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
                <h3 class="card-title">Year Change</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="<?= base_url() ?>culture_fit/edit" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Year:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="year" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#year" value="<?= $year ?>" name="year" />
                                    <div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <?php echo form_error('year', '<div class="text-danger small">', '</div>'); ?>
                            </div>
                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-primary w-100">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </form>
        </div>
        <!-- /.card -->

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Behavior Questionnaire</h3>
            </div>
            <form action="<?= base_url() ?>culture_fit/submit?year=<?= $year ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>
                                <th>NRP</th>
                                <th>FULL NAME</th>
                                <th>JABATAN</th>
                                <th>SITE</th>
                                <th>PERFORMANCE REVIEW REFERENCE</th>
                                <th>EMPLOYEE ID</th>
                                <th>EMPLOYEE</th>
                                <th>NRP</th>
                                <th>LEVEL</th>
                                <th>JABATAN</th>
                                <th>LAYER</th>
                                <th>MANAGER</th>
                                <th>NRP_MANAGER</th>
                                <th>DIVISION</th>
                                <th>WORK LOCATION</th>
                                <th>NILAI BEHAVIOUR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($culture_fit as $i_cf => $cf_i) : ?>
                                <tr data-id="<?= $cf_i['id'] ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>
                                    <td><?= $cf_i['NRP'] ?></td>
                                    <td><?= $cf_i['FullName'] ?></td>
                                    <td><?= $cf_i['oalp_name'] ?></td>
                                    <td><?= $cf_i['oa_name'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="performance_review_reference"><?= $cf_i['performance_review_reference'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="employee_id"><?= $cf_i['employee_id'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="employee"><?= $cf_i['employee'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="NRP"><?= $cf_i['NRP'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="level"><?= $cf_i['level'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="jabatan"><?= $cf_i['jabatan'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="layer"><?= $cf_i['layer'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="manager"><?= $cf_i['manager'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="NRP_manager"><?= $cf_i['NRP_manager'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="division"><?= $cf_i['division'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="work_location"><?= $cf_i['work_location'] ?></td>
                                    <td contenteditable="true" data-id="<?= $cf_i['id'] ?>" data-column="nilai_behaviour"><?= $cf_i['nilai_behaviour'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                        <div class="col-lg-3">
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
                        <div class="col-lg-3">
                            <button type="submit" class="w-100 btn btn-info">
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
    //Date picker
    $('#year').datetimepicker({
        format: 'YYYY', // Only year
        viewMode: 'years',
    });

    // Trigger submit saat tahun berubah dari picker
    $('#year').on('change.datetimepicker', function(e) {
        $(this).find('input').closest('form').submit();
    });

    let deletedRows = [];

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        $('#select-all').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $('#data-form').on('submit', function() {
            let allRows = collectTableData();
            let payload = {
                year: <?= $year ?>,
                updates: allRows.filter(r => !String(r.id).startsWith('new_')),
                deletes: deletedRows,
                creates: allRows.filter(r => String(r.id).startsWith('new_'))
            };
            $('#json_data').val(JSON.stringify(payload));
        });
    });

    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('culture_fit' . ($year ? '?year=' . $year : '')) ?>';
        }
    }

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

    // Create a new row dynamically
    function createRow() {
        showOverlayFull();
        const table = $('#datatable').DataTable();
        const newId = 'new_' + Date.now();
        const row_number_add = $('#row_number_add').val();

        // Construct the new row
        for (let index = 0; index < row_number_add; index++) {
            const rowArray = [
                '<input type="checkbox" class="row-checkbox">', // kol-1
                'New', // kol-2
                '', '', '', '', // kol-3..6
                '', // performance_review_reference (kol-7)
                '', // employee_id
                '', // employee
                '', // NRP
                '', // level
                '', // jabatan
                '', // layer
                '', // year
                '', // manager
                '', // NRP_manager
                '', // division
                '', // work_location
                '' // nilai_behaviour (kol terakhir)
            ];

            const node = table.row.add(rowArray).draw(false).node();
            $(node).attr('data-id', newId).addClass('table-success');

            // Set contenteditable untuk kolom-kolom tertentu (pakai index td)
            const editableIdx = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]; // sesuaikan
            $(node).find('td').each(function(i) {
                if (editableIdx.includes(i)) {
                    const map = {
                        6: 'performance_review_reference',
                        7: 'employee_id',
                        8: 'employee',
                        9: 'NRP',
                        10: 'level',
                        11: 'jabatan',
                        12: 'layer',
                        13: 'year',
                        14: 'manager',
                        15: 'NRP_manager',
                        16: 'division',
                        17: 'work_location',
                        18: 'nilai_behaviour'
                    };
                    $(this).attr('contenteditable', 'true').attr('data-column', map[i] || '');
                }
            });
        }

        table.columns.adjust().draw(false);

        // First, go to the last page
        table.page('last').draw('page');
        hideOverlayFull();
    }

    function collectTableData() {
        let data = [];
        let table = $('#datatable').DataTable();

        table.rows().every(function() {
            let row = $(this.node());
            data.push(collectRow(row));
        });

        $('#datatable tbody tr').each(function() {
            let row = $(this);
            let id = row.data('id');
            if (!data.find(r => r.id === id)) {
                data.push(collectRow(row));
            }
        });

        return data;
    }

    function collectRow(row) {
        let id = row.data('id');
        let rowData = {
            id: id
        };

        row.find('td[contenteditable], input, select').each(function() {
            let column = $(this).data('column');
            if (column) {
                if ($(this).is('input') || $(this).is('select')) {
                    rowData[column] = $(this).val();
                } else {
                    rowData[column] = $(this).text();
                }
            }
        });

        return rowData;
    }
</script>