<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Current RTC</h1>
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
                <a href="<?= base_url() ?>RTC/edit" class="btn btn-primary w-100">Edit</a><br><br>
                <table id="datatable" class="table table-bordered table-striped datatable-filter-column">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>NO</th>
                            <th>MATRIX POINT</th>
                            <th>SITE</th>
                            <th>LEVEL</th>
                            <th>JABATAN</th>
                            <th>FULL NAME</th>
                            <th>NRP</th>
                            <th><?= date("Y") + 1 ?></th>
                            <th><?= date("Y") + 2 ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($positions as $i_pos => $pos_i) : ?>
                            <tr>
                                <td><input type="checkbox" class="row-checkbox"></td>
                                <td><?= $i++ ?></td>
                                <td><?= $pos_i['mp_name'] ?></td>
                                <td><?= $pos_i['oa_name'] ?></td>
                                <td><?= $pos_i['oal_name'] ?></td>
                                <td><?= $pos_i['oalp_name'] ?></td>
                                <td><?= $pos_i['FullName'] ?></td>
                                <td><?= $pos_i['NRP'] ?></td>
                                <td>
                                    <?php $users = array_filter($rtcs, fn($rtc_i) => $rtc_i['year'] == date("Y") + 1 && $rtc_i['oalp_id'] == $pos_i['id']) ?>
                                    <ul>
                                        <?php foreach ($users as $i_user => $user_i) : ?>
                                            <li><?= $user_i['NRP'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <?php $users = array_filter($rtcs, fn($rtc_i) => $rtc_i['year'] == date("Y") + 2 && $rtc_i['oalp_id'] == $pos_i['id']) ?>
                                    <ul>
                                        <?php foreach ($users as $i_user => $user_i) : ?>
                                            <li><?= $user_i['NRP'] ?></li>
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