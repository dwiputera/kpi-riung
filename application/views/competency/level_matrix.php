<style>
    .select2 {
        width: 100% !important;
        max-width: 100%;
        box-sizing: border-box;
    }

    .editable-target {
        min-width: 70px;
        background: #fffbe6;
        cursor: text;
    }

    .editable-target:focus {
        outline: 2px solid #007bff;
        background: #ffffff;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Level Competency Matrix</h1>
            </div>
        </div>
    </div>
</div>

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

                        $positions = array_filter(
                            $area_pstns,
                            function ($oalp_i) use ($oal_i) {
                                return $oalp_i['area_lvl_id'] == $oal_i['oal_id'] || $oalp_i['equals'] == $oal_i['oal_id'];
                            }
                        );
                        ?>

                        <div class="tab-pane fade <?= $activeClass ?>"
                            id="custom-tabs-<?= md5($oal_i['oal_id']) ?>"
                            role="tabpanel"
                            aria-labelledby="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab">

                            <div class="mb-3">
                                <a href="<?= base_url() ?>comp_settings/level_matrix/dictionary" class="btn btn-primary w-100">Dictionary of Competency</a><br><br>
                            </div>

                            <form method="post" action="<?= base_url('comp_settings/level_matrix/comp_lvl_target/submit') ?>" class="form-level-matrix">
                                <input type="hidden" name="level_active" value="<?= md5($oal_i['oal_id']) ?>">

                                <table class="table table-bordered table-striped datatable-filter-column" data-filter-columns="1,2:multiple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Area</th>
                                            <th>Level</th>
                                            <th>Position</th>
                                            <?php foreach ($comp_levels as $cl_i) : ?>
                                                <th><?= $cl_i['name'] ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        <?php foreach ($positions as $pstn_i) : ?>
                                            <?php $area_lvl_pstn_id = (int)($pstn_i['id'] ?? 0); ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $pstn_i['oa_name'] ?></td>
                                                <td><?= $pstn_i['oal_name'] ?></td>
                                                <td><?= $pstn_i['name'] ?></td>

                                                <?php foreach ($comp_levels as $cl_i) : ?>
                                                    <?php
                                                    $comp_lvl_id = (int)$cl_i['id'];
                                                    $val = isset($pstn_i['target'][$comp_lvl_id]) ? $pstn_i['target'][$comp_lvl_id] : 0;
                                                    ?>
                                                    <td class="editable-target"
                                                        contenteditable="true"
                                                        data-area-lvl-pstn-id="<?= $area_lvl_pstn_id ?>"
                                                        data-comp-lvl-id="<?= $comp_lvl_id ?>"><?= rtrim(rtrim((string)$val, '0'), '.') ?></td>

                                                    <input type="hidden"
                                                        name="targets[<?= $area_lvl_pstn_id ?>][<?= $comp_lvl_id ?>]"
                                                        value="<?= $val ?>"
                                                        class="target-input target-input-<?= $area_lvl_pstn_id ?>-<?= $comp_lvl_id ?>">
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <button type="submit" name="proceed" value="N" class="btn btn-default w-100 show-overlay-full">Cancel</button>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="hidden" name="target_json" id="target_json">
                                        <button type="submit" id="submitBtn" class="btn btn-info w-100 show-overlay-full">Submit</button>
                                    </div>
                                </div>
                            </form>
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

        function normalizeTarget(val) {
            val = (val || '').toString().trim();
            val = val.replace(',', '.');
            val = val.replace(/[^\d.\-]/g, '');

            if (val === '') return '0';
            if (isNaN(val)) return '0';

            return val;
        }

        $(document).on('input blur paste', '.editable-target', function() {
            const $cell = $(this);
            const areaLvlPstnId = $cell.data('area-lvl-pstn-id');
            const compLvlId = $cell.data('comp-lvl-id');
            const val = normalizeTarget($cell.text());

            $cell.text(val);

            $('.target-input-' + areaLvlPstnId + '-' + compLvlId).val(val);
        });

        // simpan tab aktif saat pindah tab
        $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
            const href = $(e.target).attr('href');
            const id = href.replace('#custom-tabs-', '');
            const url = new URL(window.location.href);
            url.searchParams.set('level_active', id);
            window.history.replaceState({}, '', url);
        });
    });
</script>