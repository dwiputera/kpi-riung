<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Positional Competency Matrix (Employee • Plan • Actual • Gap)</h1>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<?php if ($admin) : ?>
    <div class="m-3 mt-0">
        <a href="<?= base_url() ?>comp_settings/position_matrix/dictionary_edit/" class="btn btn-primary w-100">Edit Dictionary of Competency</a>
    </div>
<?php endif; ?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1 no-tools">
                <ul class="nav nav-tabs" id="custom-tabs-tab" role="tablist">
                    <li class="pt-2 px-3">
                        <h3 class="card-title"><strong>Matrix Points</strong></h3>
                    </li>
                    <?php foreach ($matrix_points as $i_mp => $mp): ?>
                        <?php
                        $mpId    = (int)$mp['id'];
                        $mpIdMd5 = md5($mpId);
                        $isActive = ($matrix_position_active && $matrix_position_active === $mpIdMd5) || (!$matrix_position_active && $i_mp === 0);
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive ? 'active' : '' ?>"
                                id="tab-<?= $mpIdMd5 ?>-tab"
                                data-toggle="pill"
                                href="#tab-<?= $mpIdMd5 ?>"
                                role="tab"
                                aria-controls="tab-<?= $mpIdMd5 ?>"
                                aria-selected="<?= $isActive ? 'true' : 'false' ?>">
                                <?= $mp['name'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="custom-tabs-tabContent">
                    <?php foreach ($matrix_points as $i_mp => $mp): ?>
                        <?php
                        $mpId    = (int)$mp['id'];
                        $mpIdMd5 = md5($mpId);
                        $isActive = ($matrix_position_active && $matrix_position_active === $mpIdMd5) || (!$matrix_position_active && $i_mp === 0);

                        // kompetensi positional milik MP ini
                        $comp_positions = $competencies[$mpId] ?? []; // array of cp rows
                        // employees untuk MP ini (sudah disiapkan di controller)
                        $employees = $employees_by_mp[$mpId] ?? [];
                        ?>
                        <div class="tab-pane fade <?= $isActive ? 'show active' : '' ?>"
                            id="tab-<?= $mpIdMd5 ?>"
                            role="tabpanel"
                            aria-labelledby="tab-<?= $mpIdMd5 ?>-tab">

                            <a href="<?= base_url(($admin ? 'comp_settings' : 'competency') . "/position_matrix/dictionary/$mpIdMd5") ?>"
                                class="btn btn-primary w-100">
                                Dictionary of Competency: <strong><?= $mp['name'] ?></strong>
                            </a><br><br>

                            <table class="table table-bordered table-striped datatable-filter-column" data-filter-columns="4:multiple,5,6">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Full Name</th>
                                        <th>NRP</th>
                                        <th>Jabatan</th>
                                        <th>Level</th>
                                        <th>Area</th>
                                        <?php foreach ($comp_positions as $cp): ?>
                                            <th colspan="3"><?= $cp['name'] ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <?php foreach ($comp_positions as $cp): ?>
                                            <th>Plan</th>
                                            <th>Actual</th>
                                            <th>Gap</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1;
                                    foreach ($employees as $e): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $e['FullName'] ?></td>
                                            <td><?= $e['NRP'] ?></td>
                                            <td><?= $e['oalp_name'] ?></td>
                                            <td><?= $e['oal_name'] ?></td>
                                            <td><?= $e['oa_name'] ?></td>

                                            <?php foreach ($comp_positions as $cp): ?>
                                                <?php
                                                $cpid   = (int)$cp['id'];
                                                $plan   = $e['cp_plan'][$cpid]   ?? null;
                                                $actual = $e['cp_actual'][$cpid] ?? null;
                                                $gap    = $e['cp_gap'][$cpid]    ?? null;

                                                $bg = 'secondary';
                                                if (!is_null($gap)) {
                                                    if ($gap > 0) $bg = 'success';
                                                    elseif ($gap < 0) $bg = 'danger';
                                                    else              $bg = 'primary';
                                                }
                                                ?>
                                                <td><?= is_null($plan) ? '' : $plan ?></td>
                                                <td><?= is_null($actual) ? '' : $actual ?></td>
                                                <td class="bg-<?= $bg ?>"><?= is_null($gap) ? '' : $gap ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (empty($employees)): ?>
                                <div class="alert alert-info mt-3 mb-0">
                                    Belum ada pegawai pada matrix point ini.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /.card -->
        </div>
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