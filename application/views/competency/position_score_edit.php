<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Hard Skill Score Edit: <strong><?= $matrix_point['name'] ?></strong></h3>
            </div>
            <!-- /.card-header -->
            <form action="<?= base_url() ?>comp_settings/position_score/submit/<?= md5($matrix_point['id']) ?>" method="post" id="data-form">
                <input type="hidden" name="json_data" id="json_data">
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
                                <?php foreach ($comp_pstn as $i_cp => $cp_i) : ?>
                                    <th><?= $cp_i['name'] ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($employees as $i_e => $e_i) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= $e_i['FullName'] ?></td>
                                    <td><?= $e_i['NRP'] ?></td>
                                    <td><?= $e_i['name'] ?></td>
                                    <td><?= $e_i['oal_name'] ?></td>
                                    <td><?= $e_i['oa_name'] ?></td>
                                    <?php foreach ($comp_pstn as $i_cp => $cp_i) : ?>
                                        <td contenteditable="true" data-nrp="<?= $e_i['NRP'] ?>" data-cp_id="<?= $cp_i['id'] ?>"><?= $e_i['cp_score'][$cp_i['id']] ?></td>
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
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });

        function cancelForm() {
            if (confirm('Yakin batal?')) {
                location.href = '<?= base_url() ?>comp_settings/position_score/view/<?= md5($matrix_point['id']) ?>';
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
                        const cp_id = $td.data('cp_id');
                        const value = $td.text().trim();

                        if (nrp && cp_id) {
                            if (!data[nrp]) data[nrp] = {};
                            data[nrp][cp_id] = value;
                        }
                    });
                });
            });

            // Cari input `target_json` di dalam form yang sedang disubmit
            $(this).find('[name="json_data"]').val(JSON.stringify(data));
        });
    });
</script>