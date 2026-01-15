<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">IPA Score <?= (int)$year ?></h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- ===== Year Change ===== -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Year Change</h3>
            </div>

            <form action="<?= base_url() ?>ipa_score/edit" method="get">
                <div class="card-body">
                    <div class="form-group">
                        <label>Year:</label>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group date" id="year" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input"
                                        data-target="#year"
                                        value="<?= (int)$year ?>"
                                        name="year" />
                                    <div class="input-group-append" data-target="#year" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-primary w-100">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== CRUD TABLE ===== -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">IPA Score</h3>
            </div>

            <form action="<?= base_url() ?>ipa_score/submit?year=<?= (int)$year ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">

                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>

                                <!-- display-only -->
                                <th>NRP (VIEW)</th>
                                <th>FULL NAME</th>
                                <th>MATRIX POINT</th>
                                <th>SITE</th>
                                <th>LEVEL</th>
                                <th>JABATAN</th>

                                <!-- editable -->
                                <th>NRP</th>
                                <th>SCORE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($ipa_scores as $row) : ?>
                                <tr data-id="<?= $row['id'] ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>

                                    <!-- display-only -->
                                    <td><?= $row['NRP'] ?></td>
                                    <td><?= $row['FullName'] ?></td>
                                    <td><?= $row['mp_name'] ?></td>
                                    <td><?= $row['oa_name'] ?></td>
                                    <td><?= $row['oal_name'] ?></td>
                                    <td><?= $row['oalp_name'] ?></td>

                                    <!-- editable -->
                                    <td contenteditable="true" data-name="NRP"><?= $row['NRP'] ?></td>
                                    <td contenteditable="true" data-name="score"><?= $row['score'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="button" class="btn btn-default w-100" onclick="cancelForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" class="btn btn-danger w-100" id="btn-delete-selected">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="col-lg-3 d-flex">
                            <input type="number" class="form-control w-50" id="row_number_add" value="1">
                            <button type="button" class="btn btn-success w-50" id="btn-new-row">
                                <i class="fas fa-plus"></i> New
                            </button>
                        </div>
                        <div class="col-lg-3">
                            <button type="submit" class="btn btn-info w-100">
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
    $('#year').datetimepicker({
        format: 'YYYY',
        viewMode: 'years',
    });

    $('#year').on('change.datetimepicker', function() {
        $(this).find('input').closest('form').submit();
    });

    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('ipa_score?year=' . (int)$year) ?>';
        }
    }

    // ===== CRUD CONFIG =====
    window.DT_CRUD_CONFIG = {
        tableSelector: '#datatable',
        formSelector: '#data-form',
        jsonFieldSelector: '#json_data',

        btnNewSelector: '#btn-new-row',
        btnDeleteSelectedSelector: '#btn-delete-selected',
        rowAddCountSelector: '#row_number_add',
        selectAllSelector: '#select-all',
        rowCheckboxSelector: '.row-checkbox',

        // checkbox + NO + 6 display-only
        rowPrefixHtml: () => `
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>New</td>
            <td></td><td></td><td></td><td></td><td></td><td></td>
        `,

        deletedRowClass: 'table-danger',
        deletedOpacity: 0.7,
        newRowClass: 'table-success',

        columns: [{
                name: 'NRP',
                type: 'editable'
            },
            {
                name: 'score',
                type: 'editable'
            }
        ],

        beforeSubmit: (payload) => {
            payload.year = <?= (int)$year ?>; // inject year ke backend
            return payload;
        }
    };

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
        setupDatatableCrud($('#datatable'), window.DT_CRUD_CONFIG);
    });
</script>