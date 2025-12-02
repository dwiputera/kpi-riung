<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Health Status <?= $year ?></h1>
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
            <form action="<?= base_url() ?>health_status/edit" method="get">
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
            <form action="<?= base_url() ?>health_status/submit?year=<?= $year ?>" method="post" id="data-form">
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
                                <th>MATRIX POINT</th>
                                <th>SITE</th>
                                <th>LEVEL</th>
                                <th>JABATAN</th>
                                <th>NRP</th>
                                <th>HEALTH STATUS</th>
                                <th>HEALTH STATUS STRING</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($health_status as $i_hs => $hs_i) : ?>
                                <tr data-id="<?= $hs_i['id'] ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>
                                    <td><?= $hs_i['NRP'] ?></td>
                                    <td><?= $hs_i['FullName'] ?></td>
                                    <td><?= $hs_i['matrix_point_name'] ?></td>
                                    <td><?= $hs_i['oa_name'] ?></td>
                                    <td><?= $hs_i['oal_name'] ?></td>
                                    <td><?= $hs_i['oalp_name'] ?></td>
                                    <td contenteditable="true" data-id="<?= $hs_i['id'] ?>" data-column="NRP"><?= $hs_i['NRP'] ?></td>
                                    <td contenteditable="true" data-id="<?= $hs_i['id'] ?>" data-column="status_id"><?= $hs_i['status_id'] ?></td>
                                    <td contenteditable="true" data-id="<?= $hs_i['id'] ?>" data-column="status_string"><?= $hs_i['status_string'] ?></td>
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
            location.href = '<?= base_url('health_status' . ($year ? '?year=' . $year : '')) ?>';
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
        const row_number_add = parseInt($('#row_number_add').val(), 10) || 1;

        const editableIdx = [8, 9, 10];
        const map = {
            8: 'NRP',
            9: 'status_id',
            10: 'status_string',
        };

        for (let index = 0; index < row_number_add; index++) {
            const rowArray = [
                '<input type="checkbox" class="row-checkbox">', // kol-1
                'New', // kol-2
                '', '', '', '', '', '', // kol-3..7
                '', // NRP (kol-8)
                '', // status_id
                '', // status_string
            ];

            // 👉 JANGAN .draw(false) di sini
            const node = table.row.add(rowArray).node();

            $(node).attr('data-id', newId).addClass('table-success');

            $(node).find('td').each(function(i) {
                if (editableIdx.includes(i)) {
                    $(this)
                        .attr('contenteditable', 'true')
                        .attr('data-column', map[i] || '');
                }
            });
        }

        // 👉 Draw SEKALI saja di akhir
        table.columns.adjust().draw(false);

        // Pindah ke halaman terakhir
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