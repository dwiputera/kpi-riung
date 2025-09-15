<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Choose Matrix Point</h3>
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
                        <?php $i = 1; ?>
                        <?php foreach ($matrix_points as $i_mp => $mp_i) : ?>
                            <tr>
                                <td><a href="<?= base_url() ?>comp_settings/position_score/view/<?= md5($mp_i['id']) ?>" class="label label-primary btn btn-primary btn-xs w-100"><span><i class="fa fa-list"></i> Employee</span></a></td>
                                <td><?= $i++ ?></td>
                                <td><?= $mp_i['name'] ?></td>
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