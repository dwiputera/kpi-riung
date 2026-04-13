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
                <h1 class="m-0">Level Competency Matrix</h1>
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
                        if ($level_active) {
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
                                aria-selected="true">
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
                        if ($level_active) {
                            if ($level_active == md5($oal_i['oal_id'])) $activeClass = 'show active';
                        } else {
                            $activeClass = $i_oal == 0 ? 'show active' : '';
                        }
                        ?>

                        <?php
                        $positions = array_filter(
                            $area_pstns,
                            fn($oalp_i, $i_oalp) => $oalp_i['area_lvl_id'] == $oal_i['oal_id'] || $oalp_i['equals'] == $oal_i['oal_id'],
                            ARRAY_FILTER_USE_BOTH
                        );
                        ?>

                        <div class="tab-pane fade <?= $activeClass ?>"
                            id="custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                            role="tabpanel"
                            aria-labelledby="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab">

                            <a href="<?= base_url() ?>competency/level_matrix/dictionary" class="btn btn-primary w-100">
                                Dictionary of Competency
                            </a>

                            <br><br>

                            <table class="table table-bordered table-striped datatable-filter-column" data-filter-columns="1,2:multiple">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Area</th>
                                        <th>Level</th>
                                        <th>Position</th>

                                        <?php foreach ($comp_levels as $i_cl => $cl_i) : ?>
                                            <th><?= $cl_i['name'] ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($positions as $i_pstn => $pstn_i) : ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $pstn_i['oa_name'] ?></td>
                                            <td><?= $pstn_i['oal_name'] ?></td>
                                            <td><?= $pstn_i['name'] ?></td>

                                            <?php foreach ($comp_levels as $i_cl => $cl_i) : ?>
                                                <td>
                                                    <?= isset($pstn_i['target'][$cl_i['id']]) ? $pstn_i['target'][$cl_i['id']] : '' ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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