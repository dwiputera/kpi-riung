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

    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
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
                });
            });

            // Cari input `target_json` di dalam form yang sedang disubmit
            $(this).find('[name="json_data"]').val(JSON.stringify(data));
        });
    });
</script>