<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dictionary of Competency: <strong><?= $position['name'] ?></strong></h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <?php if ($dictionaries) : ?>
            <div class="card">
                <!-- <div class="card-header">
                    <h3 class="card-title">Dictionary</h3>
                </div> -->
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="datatable_training" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Competency</th>
                                <th>Definition</th>
                                <th>Proficiency Level</th>
                            </tr>
                            <tr>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                                <th><input type="text" placeholder="Filter..." class="column-search form-control form-control-sm" /></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($dictionaries as $dict) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= $dict['name'] ?></td>
                                    <td><?= $dict['definition'] ?></td>
                                    <td>
                                        <?php if ($dict['level_1']) : ?>
                                            <ol>
                                                <li><?= $dict['level_1'] ?></li>
                                                <li><?= $dict['level_2'] ?></li>
                                                <li><?= $dict['level_3'] ?></li>
                                                <li><?= $dict['level_4'] ?></li>
                                                <li><?= $dict['level_5'] ?></li>
                                            </ol>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
    //Date picker
    $('#month').datetimepicker({
        format: 'YYYY-MM',
        viewMode: 'months'
    });

    <?php if ($dictionaries) : ?>
        $(function() {
            $("#datatable_training").DataTable({
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                scrollX: true,
                orderCellsTop: true, // 👈 IMPORTANT for multiple thead rows
                fixedHeader: true, // Optional: keeps header visible on scroll
            }).buttons().container().appendTo('#datatable_training_wrapper .col-md-6:eq(0)');
        });

        $('#datatable_training thead tr:eq(1) th').each(function(i) {
            let input = $(this).find('input');
            if (input.length) {
                $(input).on('keyup change', function() {
                    if ($('#datatable_training').DataTable().column(i).search() !== this.value) {
                        $('#datatable_training').DataTable()
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            }
        });
    <?php endif; ?>
</script>