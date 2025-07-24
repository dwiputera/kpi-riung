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
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-tab" role="tablist">
                    <li class="pt-2 px-3">
                        <h3 class="card-title"><strong>Levels</strong></h3>
                    </li>
                    <?php foreach ($area_lvls as $i_oal => $oal_i) : ?>
                        <?php
                        $activeClass = '';
                        if ($level_active) {
                            if ($level_active == md5($oal_i['oal_id'])) $activeClass =  'active';
                        } else {
                            $activeClass = $i_oal == 0 ? 'active' : '';;
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeClass ?>" id="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab" data-toggle="pill" href="#custom-tabs-<?= md5($oal_i['oal_id']) ?>" role="tab" aria-controls="custom-tabs-<?= md5($oal_i['oal_id']) ?>" aria-selected="true"><?= $oal_i['oal_name'] ?></a>
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
                            if ($level_active == md5($oal_i['oal_id'])) $activeClass =  'show active';
                        } else {
                            $activeClass = $i_oal == 0 ? 'show active' : '';
                        }
                        ?>
                        <?php $positions = array_filter($area_pstns, fn($oalp_i, $i_oalp) => $oalp_i['area_lvl_id'] == $oal_i['oal_id'] || $oalp_i['equals'] == $oal_i['oal_id'], ARRAY_FILTER_USE_BOTH) ?>
                        <div class="tab-pane fade <?= $activeClass ?>" id="custom-tabs-<?= md5($oal_i['oal_id']) ?>" role="tabpanel" aria-labelledby="custom-tabs-<?= md5($oal_i['oal_id']) ?>-tab">
                            <a href="<?= base_url() ?>competency/level_matrix/dictionary" class="btn btn-primary w-100">Dictionary of Competency</a><br><br>
                            <?php if ($admin) : ?>
                                <button type="button" class="btn btn-primary w-100" data-toggle="modal" data-target="#modal-addCompetency" data-hash_area_lvl_id="<?= md5($oal_i['oal_id']) ?>">
                                    Add Competency
                                </button><br><br>
                                <form action="<?= base_url() ?>comp_settings/level_matrix/comp_lvl_target/submit?level_active=<?= md5($oal_i['oal_id']) ?>" method="post" id="form">
                                <?php endif; ?>
                                <table class="table table-bordered table-striped datatable-filter-column" data-filter-columns="1,2:multiple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Area</th>
                                            <th>Level</th>
                                            <th>Position</th>
                                            <?php foreach ($comp_levels as $i_cl => $cl_i) : ?>
                                                <th>
                                                    <?php if ($admin) : ?>
                                                        <a href=" <?= base_url() ?>comp_settings/level_matrix/comp_lvl/delete/<?= md5($cl_i['id']) ?>?level_active=<?= md5($oal_i['oal_id']) ?>" class="btn btn-danger btn-xs" onclick="return confirm('are you sure?')">delete</a>
                                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-editCompetency" data-hash_comp_lvl_id="<?= md5($cl_i['id']) ?>" data-hash_area_lvl_id="<?= md5($oal_i['oal_id']) ?>" data-comp_lvl_name="<?= $cl_i['name'] ?>">
                                                            Edit
                                                        </button>
                                                        <br>
                                                    <?php endif; ?>
                                                    <?= $cl_i['name'] ?>
                                                </th>
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
                                                    <td <?= $admin ? 'contenteditable="true"' : '' ?>
                                                        data-comp-id="<?= md5($cl_i['id']) ?>"
                                                        data-pos-id="<?= md5($pstn_i['id']) ?>">
                                                        <?= $pstn_i['target'][$cl_i['id']] ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php if ($admin) : ?>
                                    <br>
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
        $('.tab-pane.active .datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
        });

        $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
            const targetPaneId = $(e.target).attr('href');
            const $tables = $(targetPaneId).find('.datatable-filter-column');
            $tables.each(function() {
                setupFilterableDatatable($(this));
            });
        });

        $('#modal-editCompetency, #modal-addCompetency').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const modal = $(this);

            const hashCompLvlId = button.data('hash_comp_lvl_id') || '';
            const hashAreaLvlId = button.data('hash_area_lvl_id') || '';
            const compLvlName = button.data('comp_lvl_name') || '';

            modal.find('#hash_comp_lvl_id').val(hashCompLvlId);
            modal.find('#hash_area_lvl_id').val(hashAreaLvlId);
            modal.find('#comp_lvl_name').val(compLvlName);
        });

        $('form').on('submit', function(e) {
            const data = {};

            $('.datatable-filter-column').each(function() {
                const table = $(this).DataTable();

                table.rows().every(function() {
                    const $row = $(this.node());

                    $row.find('td[contenteditable="true"]').each(function() {
                        const $td = $(this);
                        const compId = $td.data('comp-id');
                        const posId = $td.data('pos-id');
                        const value = $td.text().trim();

                        if (compId && posId) {
                            if (!data[compId]) data[compId] = {};
                            data[compId][posId] = value;
                        }
                    });
                });
            });

            // Cari input `target_json` di dalam form yang sedang disubmit
            $(this).find('[name="target_json"]').val(JSON.stringify(data));
        });
    });
</script>