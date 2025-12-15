<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Table <strong>"<?= $table ?>"</strong></h3>
            </div>
            <form action="<?= base_url() ?>admin/database/table_submit/<?= $table_id ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>
                                <?php foreach ($columns as $i_col => $col_i) : ?>
                                    <th><?= $col_i ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php $status_str = ['P' => 'Pending', 'Y' => 'Done', 'N' => 'Canceled', 'R' => 'Reschedule']; ?>
                            <?php $status_bg = ['P' => 'none', 'Y' => 'primary', 'N' => 'danger', 'R' => 'warning']; ?>
                            <?php foreach ($rows as $i_row => $row_i) : ?>
                                <tr data-id="<?= $row_i['id'] ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>
                                    <?php foreach ($columns as $i_col => $col_i) : ?>
                                        <td contenteditable="<?= $col_i != 'id' ? 'true' : 'false' ?>" data-column="<?= $col_i ?>"><?= $row_i[$col_i] ?></td>
                                    <?php endforeach; ?>
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
    let deletedRows = [];

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));

        $('#select-all').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        $('#data-form').on('submit', function(e) {
            let allRows = collectTableData();
            let payload = {
                updates: allRows.filter(r => !String(r.id).startsWith('new_')),
                deletes: deletedRows,
                creates: allRows.filter(r => String(r.id).startsWith('new_'))
            };
            $('#json_data').val(JSON.stringify(payload));
        });
    });

    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('admin/database') ?>';
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
                <?php foreach ($columns as $i_col => $col_i) : ?> '',
                <?php endforeach; ?>
            ];

            const node = table.row.add(rowArray).node();
            $(node).attr('data-id', newId).addClass('table-success');

            // Set contenteditable untuk kolom-kolom tertentu (pakai index td)
            const map = <?= json_encode($columns) ?>;
            $(node).find('td').each(function(i) {
                if (i >= 3) {
                    $(this).attr('contenteditable', 'true').attr('data-column', map[i - 2] || '');
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

        return data;
    }

    function collectRow(row) {
        let id = row.data('id');
        let rowData = {
            id: id
        };

        row.find('td[contenteditable]').each(function() {
            let column = $(this).data('column');
            if (column) {
                rowData[column] = $(this).text();
            }
        });
        return rowData;
    }
</script>