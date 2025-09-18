<br>
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><strong>Employees</strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No</th>
                            <th>NRP</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Level</th>
                            <th>Area</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($employees as $i_e => $e_i) : ?>
                            <tr>
                                <td><a href="<?= base_url() ?>employee/employee/profile/<?= md5($e_i['NRP']) ?>" class="label label-primary" target="_blank"><span><i class="fa fa-eye"></i></span></a></td>
                                <td><?= $i++ ?></td>
                                <td><?= $e_i['NRP'] ?></td>
                                <td><?= $e_i['FullName'] ?></td>
                                <td><?= $e_i['oalp_name'] ?></td>
                                <td><?= $e_i['oal_name'] ?></td>
                                <td><?= $e_i['oa_name'] ?></td>
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