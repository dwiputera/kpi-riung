<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Soft Skill Score: <strong><?= $assess_method['name'] ?></strong></h1>
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
            <form action="<?= base_url() ?>comp_settings/level_score/year_edit/<?= md5($assess_method['id']) ?>?year=<?= $year ?>" method="get">
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
            <!-- /.card-header -->
            <form action="<?= base_url() ?>comp_settings/level_score/submit/<?= md5($assess_method['id']) ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
                <input type="hidden" name="year" value="<?= $year ?>">
                <input type="hidden" name="method_id" value="<?= $assess_method['id'] ?>">
                <div class="card-body">
                    <table id="datatable" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Full Name</th>
                                <th>NRP</th>
                                <th>Jabatan</th>
                                <th>Level</th>
                                <th>Area</th>
                                <?php foreach ($comp_lvl as $i_cl => $cl_i) : ?>
                                    <th><?= $cl_i['name'] ?></th>
                                <?php endforeach; ?>
                                <th>Vendor</th>
                                <th>Recommendation</th>
                                <th>Remarks</th>
                                <th>Score</th>
                                <th>Assessment Insight (Strength)</th>
                                <th>Assessment Insight (Development)</th>
                                <th>Talent Insight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($employees as $i_e => $e_i) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= $e_i['FullName'] ?></td>
                                    <td><?= $e_i['NRP'] ?></td>
                                    <td><?= $e_i['oalp_name'] ?></td>
                                    <td><?= $e_i['oal_name'] ?></td>
                                    <td><?= $e_i['oa_name'] ?></td>
                                    <?php foreach ($comp_lvl as $i_cl => $cl_i) : ?>
                                        <td contenteditable="true" data-nrp="<?= $e_i['NRP'] ?>" data-cl_id="<?= $cl_i['id'] ?>"><?= $e_i['cl_score'][$cl_i['id']] ?></td>
                                    <?php endforeach; ?>
                                    <td contenteditable="true" data-nrp="<?= $e_i['NRP'] ?>" data-cl_id="vendor"><?= $e_i['vendor'] ?></td>
                                    <td contenteditable="true" data-nrp="<?= $e_i['NRP'] ?>" data-cl_id="recommendation"><?= $e_i['recommendation'] ?></td>
                                    <td contenteditable="true" data-nrp="<?= $e_i['NRP'] ?>" data-cl_id="remarks"><?= $e_i['remarks'] ?></td>
                                    <td contenteditable="true" data-nrp="<?= $e_i['NRP'] ?>" data-cl_id="score"><?= $e_i['score'] ?></td>
                                    <td class="wysiwyg-cell"
                                        data-nrp="<?= $e_i['NRP'] ?>"
                                        data-cl_id="assessment_insight_strength">

                                        <button type="button" class="btn btn-xs btn-secondary btn-edit-wysiwyg">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <!-- simpan HTML aman di textarea hidden -->
                                        <textarea class="d-none wysiwyg-store"><?= htmlspecialchars($e_i['assessment_insight_strength'] ?? '', ENT_NOQUOTES) ?></textarea>

                                        <div class="wysiwyg-preview text-muted small mt-1"></div>
                                    </td>
                                    <td class="wysiwyg-cell"
                                        data-nrp="<?= $e_i['NRP'] ?>"
                                        data-cl_id="assessment_insight_development">

                                        <button type="button" class="btn btn-xs btn-secondary btn-edit-wysiwyg">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <!-- simpan HTML aman di textarea hidden -->
                                        <textarea class="d-none wysiwyg-store"><?= htmlspecialchars($e_i['assessment_insight_development'] ?? '', ENT_NOQUOTES) ?></textarea>

                                        <div class="wysiwyg-preview text-muted small mt-1"></div>
                                    </td>
                                    <td class="wysiwyg-cell"
                                        data-nrp="<?= $e_i['NRP'] ?>"
                                        data-cl_id="talent_insight">

                                        <button type="button" class="btn btn-xs btn-secondary btn-edit-wysiwyg">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <!-- simpan HTML aman di textarea hidden -->
                                        <textarea class="d-none wysiwyg-store"><?= htmlspecialchars($e_i['talent_insight'] ?? '', ENT_NOQUOTES) ?></textarea>

                                        <div class="wysiwyg-preview text-muted small mt-1"></div>
                                    </td>
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
                        <div class="col-lg-9">
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

    <div class="modal fade" id="wysiwygModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Insight</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <textarea id="wysiwygEditor"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSaveWysiwyg">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>
<!-- /.content -->

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-bs4.min.css') ?>">
<script src="<?= base_url('assets/plugins/summernote/summernote-bs4.min.js') ?>"></script>

<style>
    td.wysiwyg-cell .wysiwyg-preview {
        max-height: 90px;
        overflow: auto;
        padding: 6px 8px;
        border: 1px dashed #ddd;
        border-radius: 4px;
        background: #fafafa;
    }

    td.wysiwyg-cell .wysiwyg-preview p:last-child {
        margin-bottom: 0;
    }
</style>

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

    $(function() {

        let $activeWysiwygCell = null;

        function stripHtml(html) {
            return $('<div>').html(html || '').text();
        }

        function getWysiwygValue($cell) {
            return ($cell.find('.wysiwyg-store').val() || '').trim();
        }

        function setWysiwygValue($cell, html) {
            $cell.find('.wysiwyg-store').val(html || '');
        }

        function renderWysiwygPreview($cell) {
            const html = getWysiwygValue($cell);

            if (!html) {
                $cell.find('.wysiwyg-preview').html('<span class="text-muted">(empty)</span>');
                return;
            }

            // render HTML (wysiwyg)
            $cell.find('.wysiwyg-preview').html(html);
        }

        // render preview saat load
        $('.wysiwyg-cell').each(function() {
            renderWysiwygPreview($(this));
        });

        // klik Edit
        $(document).on('click', '.btn-edit-wysiwyg', function() {
            $activeWysiwygCell = $(this).closest('.wysiwyg-cell');
            const html = getWysiwygValue($activeWysiwygCell);

            $('#wysiwygModal').modal('show');

            if (!$('#wysiwygEditor').next('.note-editor').length) {
                $('#wysiwygEditor').summernote({
                    height: 350,
                    placeholder: 'Tulis insight di sini...',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['codeview']]
                    ]
                });
            }

            $('#wysiwygEditor').summernote('code', html);
        });

        // save modal
        $('#btnSaveWysiwyg').on('click', function() {
            if (!$activeWysiwygCell) return;

            const html = $('#wysiwygEditor').summernote('code') || '';
            setWysiwygValue($activeWysiwygCell, html);
            renderWysiwygPreview($activeWysiwygCell);

            $('#wysiwygModal').modal('hide');
        });

        // optional: bersihin editor saat modal ditutup
        $('#wysiwygModal').on('hidden.bs.modal', function() {
            $activeWysiwygCell = null;
        });

        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
            const dt = $(this).DataTable();

            function renderVisible() {
                $(dt.table().body()).find('td.wysiwyg-cell').each(function() {
                    renderWysiwygPreview($(this));
                });
            }

            dt.on('draw.dt', renderVisible);
            renderVisible();
        });

        function cancelForm() {
            if (confirm('Yakin batal?')) {
                location.href = '<?= base_url() ?>comp_settings/level_score/year/<?= md5($assess_method['id']) ?>?year=<?= ($year ? '?year=' . $year : '') ?>';
            }
        }

        $('form').on('submit', function(e) {
            const data = {};

            $('.datatable-filter-column').each(function() {
                const table = $(this).DataTable();

                table.rows().every(function() {
                    const $row = $(this.node());

                    // 1) contenteditable biasa
                    $row.find('td[contenteditable="true"]').each(function() {
                        const $td = $(this);
                        const nrp = $td.data('nrp');
                        const cl_id = $td.data('cl_id');
                        const value = $td.text().trim();

                        if (nrp && cl_id) {
                            if (!data[nrp]) data[nrp] = {};
                            data[nrp][cl_id] = value;
                        }
                    });

                    // 2) wysiwyg cells (HTML)
                    $row.find('td.wysiwyg-cell').each(function() {
                        const $td = $(this);
                        const nrp = $td.data('nrp');
                        const cl_id = $td.data('cl_id');
                        const html = ($td.find('.wysiwyg-store').val() || '').trim();

                        if (nrp && cl_id) {
                            if (!data[nrp]) data[nrp] = {};
                            data[nrp][cl_id] = html;
                        }
                    });
                });
            });

            $(this).find('[name="json_data"]').val(JSON.stringify(data));
        });
    });
</script>