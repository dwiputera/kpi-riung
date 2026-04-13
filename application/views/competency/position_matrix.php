<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Positional Competency Matrix</h1>
            </div>
        </div>
    </div>
</div>

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
                        <h3 class="card-title"><strong>Levels</strong></h3>
                    </li>
                    <?php foreach ($matrix_points as $i_mp => $mp): ?>
                        <?php
                        $mpIdMd5 = md5($mp['id']);
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
                        $mpId = $mp['id'];
                        $mpIdMd5 = md5($mpId);
                        $isActive = ($matrix_position_active && $matrix_position_active === $mpIdMd5) || (!$matrix_position_active && $i_mp === 0);
                        $positions = $mp['subordinates'];
                        $comp_positions = $competencies[$mpId];
                        ?>
                        <div class="tab-pane fade <?= $isActive ? 'show active' : '' ?>"
                            id="tab-<?= $mpIdMd5 ?>"
                            role="tabpanel"
                            aria-labelledby="tab-<?= $mpIdMd5 ?>-tab">

                            <a href="<?= base_url(($admin ? 'comp_settings' : 'competency') . "/position_matrix/dictionary/$mpIdMd5") ?>"
                                class="btn btn-primary w-100">
                                Dictionary of Competency: <strong><?= $mp['name'] ?></strong>
                            </a>
                            <br><br>

                            <?php if ($admin): ?>
                                <form action="<?= base_url("comp_settings/position_matrix/comp_pstn_target/submit?matrix_position_active=$mpIdMd5") ?>" method="post">
                                <?php endif; ?>

                                <table class="table table-bordered table-striped datatable-filter-column" data-filter-columns="1,2:multiple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Area</th>
                                            <th>Level</th>
                                            <th>Position</th>
                                            <?php foreach ($comp_positions as $cp): ?>
                                                <th><?= $cp['name'] ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        <?php foreach ($positions as $i_pstn => $pstn_i): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $pstn_i['oa_name'] ?></td>
                                                <td><?= $pstn_i['oal_name'] ?></td>
                                                <td><?= $pstn_i['name'] ?></td>
                                                <?php foreach ($comp_positions as $cp): ?>
                                                    <td <?= $admin ? 'contenteditable="true"' : '' ?>
                                                        data-comp-id="<?= md5($cp['id']) ?>"
                                                        data-pos-id="<?= md5($pstn_i['id']) ?>">
                                                        <?= $pstn_i['target'][$cp['id']] ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if ($admin): ?>
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
        $('.datatable-filter-column').each(function() {
            setupFilterableDatatable($(this));
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

            $(this).find('[name="target_json"]').val(JSON.stringify(data));
        });
    });
</script>