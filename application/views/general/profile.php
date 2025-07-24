<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Profile</h3>
            </div>
            <div class="card-body">
                <table id="" class="table table-borderless">
                    <tbody>
                        <tr>
                            <td>Nama</td>
                            <td><?= $user['FullName'] ?></td>
                        </tr>
                        <tr>
                            <td>NRP</td>
                            <td><?= $user['NRP'] ?></td>
                        </tr>
                        <?php if ($position) : ?>
                            <tr>
                                <td>Jabatan</td>
                                <td><?= $position['name'] ?></td>
                            </tr>
                            <tr>
                                <td>Level Jabatan</td>
                                <td><?= $position['oal_name'] ?></td>
                            </tr>
                            <tr>
                                <td>Area</td>
                                <td><?= $position['oa_name'] ?></td>
                            </tr>
                        <?php else: ?>
                            <tr class="text-center bg-danger">
                                <td colspan="2"><strong>Belum Masuk SO</strong></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <?php if ($competency_matrix_position) : ?>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Competency Matrix: <strong><?= $competency_matrix_position['name'] ?></strong></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="" class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Competency</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($comp_pstn as $i_cp => $cp_i) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= $cp_i['name'] ?></td>
                                    <td><?= $competency_matrix_position['target'][$cp_i['id']] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        <?php endif; ?>

        <?php if ($competency_matrix_level) : ?>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Competency Matrix: <strong><?= $competency_matrix_level['oal_name'] ?></strong></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php
                    $peran = array_filter($comp_lvl, fn($cl_i, $i_cl) => $cl_i['type'] == 'role', ARRAY_FILTER_USE_BOTH);
                    $perilaku = array_filter($comp_lvl, fn($cl_i, $i_cl) => $cl_i['type'] == 'behavior', ARRAY_FILTER_USE_BOTH);
                    ?>
                    <div class="row">
                        <div class="col-lg-6">
                            <table id="" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <td colspan="2" class="text-center"><strong>Peran</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Competency</th>
                                        <th>Target</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($peran as $i_cl => $cl_i) : ?>
                                        <tr>
                                            <td><?= $cl_i['name'] ?></td>
                                            <td><?= $competency_matrix_level['target'][$cl_i['id']] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-lg-6">
                            <table id="" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <td colspan="2" class="text-center"><strong>Perilaku</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Competency</th>
                                        <th>Target</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($perilaku as $i_cl => $cl_i) : ?>
                                        <tr>
                                            <td><?= $cl_i['name'] ?></td>
                                            <td><?= $competency_matrix_level['target'][$cl_i['id']] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        <?php endif; ?>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<!-- Page specific script -->
<script>
    $(function() {
        $(".datatable").each(function() {
            const table = $(this).DataTable({
                autoWidth: false,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                scrollX: true,
                orderCellsTop: true,
                fixedHeader: true,
                buttons: ["copy", "csv", "excel", "pdf"]
            });

            // Append buttons to a suitable container relative to this table
            table.buttons().container().appendTo(
                $(this).closest('.dataTables_wrapper').find('.col-md-6').eq(0)
            );
        });
    });
</script>