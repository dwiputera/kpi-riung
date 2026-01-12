<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current Health Status</h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Health Status</h3>
            </div>
            <div class="card-body">
                <a href="<?= base_url() ?>tour_of_duty/edit" class="btn btn-primary w-100">Edit</a><br><br>
                <table id="datatable" class="table table-bordered table-strsiped datatable-filter-column">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NRP</th>
                            <th>Date</th>
                            <th>Position</th>
                            <th>Matrix Point(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($tour_of_duties as $i_tod => $tod_i) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $tod_i['NRP'] ?></td>
                                <td><?= $tod_i['date'] ?></td>
                                <td><?= $tod_i['position'] ?></td>
                                <td>
                                    <ul>
                                        <?php foreach ($tod_i['matrix_points'] as $i_mp => $mp_i) : ?>
                                            <li><?= $mp_i['name'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
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
        setupFilterableDatatable($('.datatable-filter-column'));
    });
</script>