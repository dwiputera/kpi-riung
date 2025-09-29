<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current Hard Skill Score</h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><strong><?= $matrix_point['name'] ?></strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <a href="<?= base_url() ?>comp_settings/position_score/year_edit/<?= md5($matrix_point['id']) ?>?year=<?= $year ?>" class="btn btn-primary w-100"><i class="fa fa-edit"></i> Edit</a><br><br>
                <table id="" class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Full Name</th>
                            <th>NRP</th>
                            <th>Jabatan</th>
                            <th>Level</th>
                            <th>Area</th>
                            <?php foreach ($comp_pstn as $i_cp => $cp_i) : ?>
                                <th colspan="3"><?= $cp_i['name'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <?php foreach ($comp_pstn as $i_cp => $cp_i) : ?>
                                <th>Plan</th>
                                <th>Actual</th>
                                <th>Gap</th>
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
                                    <?php
                                    $plan  = $e_i['cp_target'][$cp_i['id']];
                                    $actual = $e_i['cp_score'][$cp_i['id']];
                                    $gap   = (is_numeric($actual) && is_numeric($plan)) ? ($actual - $plan) : null;
                                    $bg    = is_null($gap) ? 'secondary' : ($gap > 0 ? 'success' : ($gap < 0 ? 'danger' : 'primary'));
                                    ?>
                                    <td><?= is_null($plan) ? '' : $plan ?></td>
                                    <td><?= is_null($actual) ? '' : $actual ?></td>
                                    <td class="bg-<?= $bg ?>"><?= is_null($gap) ? '' : $gap ?></td>
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

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>

<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    });
</script>