<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current Culture Fit</h1>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Behavior Questionnaire</h3>
            </div>
            <div class="card-body">
                <a href="<?= base_url() ?>culture_fit/edit" class="btn btn-primary w-100">Edit</a><br><br>
                <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NRP</th>
                            <th>FULL NAME</th>
                            <th>MATRIX POINT</th>
                            <th>SITE</th>
                            <th>LEVEL</th>
                            <th>JABATAN</th>
                            <th>PERFORMANCE REVIEW REFERENCE</th>
                            <th>EMPLOYEE ID</th>
                            <th>EMPLOYEE</th>
                            <th>LEVEL</th>
                            <th>JABATAN</th>
                            <th>LAYER</th>
                            <th>TAHUN</th>
                            <th>MANAGER</th>
                            <th>NRP_MANAGER</th>
                            <th>DIVISION</th>
                            <th>WORK LOCATION</th>
                            <th>NILAI BEHAVIOUR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($culture_fit as $i_cf => $cf_i) : ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $cf_i['NRP'] ?></td>
                                <td><?= $cf_i['FullName'] ?></td>
                                <td><?= $cf_i['matrix_point_name'] ?></td>
                                <td><?= $cf_i['oa_name'] ?></td>
                                <td><?= $cf_i['oal_name'] ?></td>
                                <td><?= $cf_i['oalp_name'] ?></td>
                                <td><?= $cf_i['performance_review_reference'] ?></td>
                                <td><?= $cf_i['employee_id'] ?></td>
                                <td><?= $cf_i['employee'] ?></td>
                                <td><?= $cf_i['level'] ?></td>
                                <td><?= $cf_i['jabatan'] ?></td>
                                <td><?= $cf_i['layer'] ?></td>
                                <td><?= $cf_i['year'] ?></td>
                                <td><?= $cf_i['manager'] ?></td>
                                <td><?= $cf_i['NRP_manager'] ?></td>
                                <td><?= $cf_i['division'] ?></td>
                                <td><?= $cf_i['work_location'] ?></td>
                                <td><?= $cf_i['nilai_behaviour'] ?></td>
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