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

                                    <!-- display-only columns (tetap dipertahankan) -->
                                    <td><?= $cf_i['NRP'] ?></td>
                                    <td><?= $cf_i['FullName'] ?></td>
                                    <td><?= $cf_i['matrix_point_name'] ?></td>
                                    <td><?= $cf_i['oa_name'] ?></td>
                                    <td><?= $cf_i['oal_name'] ?></td>
                                    <td><?= $cf_i['oalp_name'] ?></td>

                                    <!-- editable columns: ganti data-column -> data-name -->
                                    <td contenteditable="true" data-name="performance_review_reference"><?= $cf_i['performance_review_reference'] ?></td>
                                    <td contenteditable="true" data-name="employee_id"><?= $cf_i['employee_id'] ?></td>
                                    <td contenteditable="true" data-name="employee"><?= $cf_i['employee'] ?></td>
                                    <td contenteditable="true" data-name="NRP"><?= $cf_i['NRP'] ?></td>
                                    <td contenteditable="true" data-name="level"><?= $cf_i['level'] ?></td>
                                    <td contenteditable="true" data-name="jabatan"><?= $cf_i['jabatan'] ?></td>
                                    <td contenteditable="true" data-name="layer"><?= $cf_i['layer'] ?></td>
                                    <td contenteditable="true" data-name="manager"><?= $cf_i['manager'] ?></td>
                                    <td contenteditable="true" data-name="NRP_manager"><?= $cf_i['NRP_manager'] ?></td>
                                    <td contenteditable="true" data-name="division"><?= $cf_i['division'] ?></td>
                                    <td contenteditable="true" data-name="work_location"><?= $cf_i['work_location'] ?></td>
                                    <td contenteditable="true" data-name="nilai_behaviour"><?= $cf_i['nilai_behaviour'] ?></td>
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
        <!-- /.card -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

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
            location.href = '<?= base_url('culture_fit' . ($year ? '?year=' . $year : '')) ?>';
        }
    }

    // ===== CRUD CONFIG (datatable-filter-column.js) =====
    // table punya 2 kolom fixed di depan: checkbox + NO
    // engine hanya membangun cell untuk "kolom data" setelah prefix.
    // Di halaman ini, kita hanya CRUD-kan kolom editable (mulai PERF REVIEW REF sampai NILAI BEHAVIOUR).
    // Kolom display-only tidak diikutkan ke payload karena tidak ada data-name input/td untuk dibaca.

    window.DT_CRUD_CONFIG = {
        tableSelector: '#datatable',
        formSelector: '#data-form',
        jsonFieldSelector: '#json_data',

        btnNewSelector: '#btn-new-row',
        btnDeleteSelectedSelector: '#btn-delete-selected',
        rowAddCountSelector: '#row_number_add',
        selectAllSelector: '#select-all',
        rowCheckboxSelector: '.row-checkbox',

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

        // ✅ urutan harus sama dengan kolom editable yang ada di thead setelah 8 kolom pertama
        columns: [{
                name: 'performance_review_reference',
                type: 'editable'
            },
            {
                name: 'employee_id',
                type: 'editable'
            },
            {
                name: 'employee',
                type: 'editable'
            },
            {
                name: 'NRP',
                type: 'editable'
            },
            {
                name: 'level',
                type: 'editable'
            },
            {
                name: 'jabatan',
                type: 'editable'
            },
            {
                name: 'layer',
                type: 'editable'
            },
            {
                name: 'manager',
                type: 'editable'
            },
            {
                name: 'NRP_manager',
                type: 'editable'
            },
            {
                name: 'division',
                type: 'editable'
            },
            {
                name: 'work_location',
                type: 'editable'
            },
            {
                name: 'nilai_behaviour',
                type: 'editable'
            }
        ],

        // Inject year (biar sama kayak payload lama)
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