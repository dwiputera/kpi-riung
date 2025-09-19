<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Choose Table</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>Matrix Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $i_t => $t_i) : ?>
                            <tr>
                                <td><a href="<?= base_url() ?>admin/database/table/<?= $i_t ?>" class="label label-primary btn btn-primary btn-xs w-100"><span><i class="fa fa-list"></i></span></a></td>
                                <td><?= $i_t ?></td>
                                <td><?= $t_i ?></td>
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

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    });
</script>