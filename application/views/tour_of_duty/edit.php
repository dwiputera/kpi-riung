<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Tour Of Duty</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tour Of Duty</h3>
            </div>

            <!-- FORM submit (payload JSON) -->
            <form action="<?= base_url() ?>tour_of_duty/submit" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">

                <div class="card-body">
                    <button type="button" class="btn btn-primary w-100" data-toggle="modal" data-target="#modalMatrixPointHelp">
                        <i class="fas fa-list"></i> List Matrix Point ID
                    </button><br><br>

                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>NO</th>

                                <!-- display-only (opsional, bisa kamu hapus kalau gak perlu) -->
                                <th>NRP (View)</th>

                                <!-- editable -->
                                <th>NRP</th>
                                <th>Date</th>
                                <th>Matrix Point ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($tour_of_duties as $i_tod => $tod_i) : ?>
                                <?php
                                // gabungkan matrix_points jadi string "1,2,3"
                                $mp_ids = [];
                                if (!empty($tod_i['matrix_points'])) {
                                    foreach ($tod_i['matrix_points'] as $mp_i) {
                                        $mp_ids[] = $mp_i['matrix_point_id'];
                                    }
                                }
                                $mp_string = implode(',', $mp_ids);
                                ?>
                                <tr data-id="<?= $tod_i['id'] ?? '' ?>">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td><?= $i++ ?></td>

                                    <!-- display-only -->
                                    <td><?= $tod_i['NRP'] ?></td>

                                    <!-- editable -->
                                    <td contenteditable="true" data-name="NRP"><?= $tod_i['NRP'] ?></td>
                                    <td contenteditable="true" data-name="date"><?= $tod_i['date'] ?></td>

                                    <!-- editable: isi "1,2,3" -->
                                    <td contenteditable="true" data-name="matrix_points"><?= $mp_string ?></td>
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
                            <input type="number" class="form-control w-50" id="row_number_add" name="row_number_add" value="1" min="1">
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

        <div class="modal fade" id="modalMatrixPointHelp" tabindex="-1" role="dialog" aria-labelledby="modalMatrixPointHelpLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMatrixPointHelpLabel">Daftar Matrix Point (ID)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            Cara isi kolom <b>Matrix Point(s)</b>: ketik ID dipisahkan koma. Contoh: <code>1,2,3</code>
                        </div>

                        <table id="datatable-mp" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Matrix Point</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($matrix_points)) : ?>
                                    <?php foreach ($matrix_points as $mp) : ?>
                                        <tr>
                                            <td><?= $mp['id'] ?? $mp['matrix_point_id'] ?? '' ?></td>
                                            <td><?= $mp['matrix_point_name'] ?? $mp['name'] ?? '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    function cancelForm() {
        if (confirm('Yakin batal?')) {
            location.href = '<?= base_url('tour_of_duty') ?>';
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

        // Prefix: checkbox + NO + 1 kolom display-only (NRP View)
        rowPrefixHtml: () => `
            <td><input type="checkbox" class="row-checkbox"></td>
            <td>New</td>
            <td></td>
        `,

        deletedRowClass: 'table-danger',
        deletedOpacity: 0.7,
        newRowClass: 'table-success',

        // Kolom editable setelah prefix (urut harus sama seperti thead setelah prefix)
        columns: [{
                name: 'NRP',
                type: 'editable'
            },
            {
                name: 'date',
                type: 'editable'
            }, // saran format: YYYY-MM-DD
            {
                name: 'position',
                type: 'editable'
            },
            {
                name: 'matrix_points',
                type: 'editable'
            } // isi: "1,2,3"
        ],

        // Hook optional: normalisasi sebelum submit (trim)
        beforeSubmit: (payload) => {
            const trimRow = (r) => {
                Object.keys(r || {}).forEach(k => {
                    if (typeof r[k] === 'string') r[k] = r[k].trim();
                });
                return r;
            };
            payload.creates = (payload.creates || []).map(trimRow);
            payload.updates = (payload.updates || []).map(trimRow);
            return payload;
        }
    };

    $(function() {
        setupFilterableDatatable($('.datatable-filter-column'));
        setupDatatableCrud($('#datatable'), window.DT_CRUD_CONFIG);
    });
</script>