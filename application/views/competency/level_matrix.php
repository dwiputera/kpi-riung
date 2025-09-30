<style>
    .select2 {
        width: 100% !important;
        max-width: 100%;
        box-sizing: border-box;
    }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Level Competency - Employee (Plan • Actual • Gap)</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1 no-tools">
                <ul class="nav nav-tabs" id="custom-tabs-tab" role="tablist">
                    <li class="pt-2 px-3">
                        <h3 class="card-title"><strong>Levels</strong></h3>
                    </li>
                    <?php foreach ($area_lvls as $i_oal => $oal_i) : ?>
                        <?php
                        $activeClass = '';
                        if (!empty($level_active)) {
                            if ($level_active == md5($oal_i['oal_id'])) $activeClass = 'active';
                        } else {
                            $activeClass = $i_oal == 0 ? 'active' : '';
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>"
                                id="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab"
                                data-toggle="pill"
                                href="#custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                                role="tab"
                                aria-controls="custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                                aria-selected="<?= $activeClass ? 'true' : 'false' ?>">
                                <?= $oal_i['oal_name'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="custom-tabs-tabContent">
                    <?php foreach ($area_lvls as $i_oal => $oal_i) : ?>
                        <?php
                        $activeClass = '';
                        if (!empty($level_active)) {
                            if ($level_active == md5($oal_i['oal_id'])) $activeClass = 'show active';
                        } else {
                            $activeClass = $i_oal == 0 ? 'show active' : '';
                        }

                        // Filter: hanya pegawai di level ini
                        $emp_in_level = array_values(array_filter($employees, function ($e) use ($oal_i) {
                            return isset($e['oal_id']) && (int)$e['oal_id'] === (int)$oal_i['oal_id'];
                        }));
                        ?>
                        <div class="tab-pane fade <?= $activeClass ?>"
                            id="custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                            role="tabpanel"
                            aria-labelledby="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab">

                            <table class="table table-bordered table-striped datatable-filter-column"
                                data-filter-columns="4:multiple,5,6">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Full Name</th>
                                        <th>NRP</th>
                                        <th>Jabatan</th>
                                        <th>Level</th>
                                        <th>Area</th>
                                        <?php foreach ($comp_levels as $cl) : ?>
                                            <th colspan="3"><?= $cl['name'] ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <?php foreach ($comp_levels as $cl) : ?>
                                            <th>Plan</th>
                                            <th>Actual</th>
                                            <th>Gap</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($emp_in_level as $e) : ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= $e['FullName'] ?></td>
                                            <td><?= $e['NRP'] ?></td>
                                            <td><?= $e['oalp_name'] ?></td>
                                            <td><?= $e['oal_name'] ?></td>
                                            <td><?= $e['oa_name'] ?></td>

                                            <?php foreach ($comp_levels as $cl) : ?>
                                                <?php
                                                $clid   = (int)$cl['id'];
                                                $plan   = $e['cl_target'][$clid] ?? null;
                                                $actual = $e['cl_score'][$clid]  ?? null;
                                                $gap    = $e['cl_gap'][$clid]    ?? null;

                                                $bg = 'secondary';
                                                if (!is_null($gap)) {
                                                    if ($gap > 0)      $bg = 'success';
                                                    elseif ($gap < 0) $bg = 'danger';
                                                    else               $bg = 'primary';
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

                            <?php if (empty($emp_in_level)) : ?>
                                <div class="alert alert-info mt-3 mb-0">
                                    Belum ada pegawai pada level ini.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/select2-fuzzy.js') ?>"></script>
<script src="<?= base_url('assets/js/datatable-filter-column.js') ?>"></script>
<script>
    $(function() {
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });
    });
</script>