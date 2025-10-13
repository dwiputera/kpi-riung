<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Create Data</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php foreach ($columns as $column) : ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($createData as $i_cd => $cd_i) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <?php foreach ($columns as $column) : ?>
                                    <td><?= $cd_i[$column] ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Terminate Data</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php foreach ($columns as $column) : ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($terminateData as $i_td => $td_i) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <?php foreach ($columns as $column) : ?>
                                    <td><?= $td_i[$column] ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Auto Update Data</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php foreach ($columns as $column) : ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                            <?php foreach ($columns as $column) : ?>
                                <?php if (!in_array($column, array('NRP', 'FullName', 'BirthDate'))) : ?>
                                    <th>NEW <?= $column ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($oldAutoData as $i_od => $od_i) : ?>
                            <?php $terminate = false; ?>
                            <?php if ($newAutoData[$od_i['NRP']]['ActionType'] == 'Terminate') $terminate = true; ?>
                            <tr class="bg-<?= $od_i['ActionType'] && $newAutoData[$od_i['NRP']]['ActionType'] == 'Terminate' ? 'danger' : 'none' ?>">
                                <td><?= $i++ ?></td>
                                <?php foreach ($columns as $column) : ?>
                                    <td class="bg-<?= !$terminate && $od_i[$column . '_flag'] ? 'warning' : 'none' ?>">
                                        <?= $od_i[$column] ?>
                                    </td>
                                <?php endforeach; ?>
                                <?php foreach ($columns as $column) : ?>
                                    <?php if (!in_array($column, array('NRP', 'FullName', 'BirthDate'))) : ?>
                                        <td class="bg-<?= !$terminate && $newAutoData[$od_i['NRP']][$column . '_flag'] ? 'primary' : 'none' ?>">
                                            <?= $newAutoData[$od_i['NRP']][$column] ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Update Data</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php foreach ($columns as $column) : ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                            <?php foreach ($columns as $column) : ?>
                                <?php if (!in_array($column, array('NRP', 'FullName', 'BirthDate'))) : ?>
                                    <th>NEW <?= $column ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($oldData as $i_od => $od_i) : ?>
                            <?php $terminate = false; ?>
                            <?php if ($newData[$od_i['NRP']]['ActionType'] == 'Terminate') $terminate = true; ?>
                            <tr class="bg-<?= $od_i['ActionType'] && $newData[$od_i['NRP']]['ActionType'] == 'Terminate' ? 'danger' : 'none' ?>">
                                <td><?= $i++ ?></td>
                                <?php foreach ($columns as $column) : ?>
                                    <td class="bg-<?= !$terminate && $od_i[$column . '_flag'] ? 'warning' : 'none' ?>">
                                        <?= $od_i[$column] ?>
                                    </td>
                                <?php endforeach; ?>
                                <?php foreach ($columns as $column) : ?>
                                    <?php if (!in_array($column, array('NRP', 'FullName', 'BirthDate'))) : ?>
                                        <td class="bg-<?= !$terminate && $newData[$od_i['NRP']][$column . '_flag'] ? 'primary' : 'none' ?>">
                                            <?= $newData[$od_i['NRP']][$column] ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
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