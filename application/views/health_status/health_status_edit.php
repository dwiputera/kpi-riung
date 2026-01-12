<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Health Status <?= $year ?></h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Year Change</h3>
            </div>

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
            </form>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Behavior Questionnaire</h3>
            </div>

            <form action="<?= base_url() ?>health_status/submit?year=<?= $year ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">

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

                                    <!-- display-only -->
                                    <td><?= $hs_i['NRP'] ?></td>
                                    <td><?= $hs_i['FullName'] ?></td>
                                    <td><?= $hs_i['matrix_point_name'] ?></td>
                                    <td><?= $hs_i['oa_name'] ?></td>
                                    <td><?= $hs_i['oal_name'] ?></td>
                                    <td><?= $hs_i['oalp_name'] ?></td>

                                    <!-- editable (pakai data-name, bukan data-column) -->
                                    <td contenteditable="true" data-name="NRP"><?= $hs_i['NRP'] ?></td>
                                    <td contenteditable="true" data-name="status_id"><?= $hs_i['status_id'] ?></td>
                                    <td contenteditable="true" data-name="status_string"><?= $hs_i['status_string'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-default" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>

                        <div class="col-lg-3">
                            <button type="button" class="w-100 btn btn-danger" id="btn-delete-selected">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>

                        <div class="col-lg-3 d-flex">
                            <input type="number" class="form-control w-50" id="row_number_add" name="row_number_add" value="1">
                            <button type="button" class="w-50 btn btn-success" id="btn-new-row">
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

    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    // Date picker
    $('#year').datetimepicker({
        format: 'YYYY',
        viewMode: 'years',
    });

    // Trigger submit saat tahun berubah dari picker
    $('#year').on('change.datetimepicker', function(e) {
        $(this).find('input').closest('form').submit();
    });

    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('health_status' . ($year ? '?year=' . $year : '')) ?>';
        }
    }

    // ===== CRUD CONFIG (datatable-filter-column.js) =====
    window.DT_CRUD_CONFIG = {
        tableSelector: '#datatable',
        formSelector: '#data-form',
        jsonFieldSelector: '#json_data',

        btnNewSelector: '#btn-new-row',
        btnDeleteSelectedSelector: '#btn-delete-selected',
        rowAddCountSelector: '#row_number_add',
        selectAllSelector: '#select-all',
        rowCheckboxSelector: '.row-checkbox',

        // 8 kolom prefix: checkbox + NO + 6 kolom display-only (NRP..JABATAN)
        rowPrefixHtml: () => `
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>New</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        `,

        deletedRowClass: 'table-danger',
        deletedOpacity: 0.7,
        newRowClass: 'table-success',

        // ✅ kolom editable setelah prefix (urut harus sama dengan thead)
        columns: [{
                name: 'NRP',
                type: 'editable'
            },
            {
                name: 'status_id',
                type: 'editable'
            },
            {
                name: 'status_string',
                type: 'editable'
            }
        ],

        // supaya payload tetap bawa year seperti versi lama
        beforeSubmit: (payload) => {
            payload.year = <?= (int)$year ?>;
            return payload;
        }
    };

    $(function() {
        // init DataTables + filter column
        setupFilterableDatatable($('.datatable-filter-column'));

        // init CRUD engine
        setupDatatableCrud($('#datatable'), window.DT_CRUD_CONFIG);
    });
</script>